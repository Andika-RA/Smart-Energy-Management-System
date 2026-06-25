require("dotenv").config();

const express = require("express");
const jwt = require("jsonwebtoken");
const mysql = require("mysql2/promise");
const bcrypt = require("bcryptjs");
const crypto = require("crypto");

function parseDuration(durationStr) {
  const matches = durationStr.toString().match(/^(\d+)([hmds])$/);
  if (!matches) return 3600 * 1000;
  const value = parseInt(matches[1]);
  const unit = matches[2];
  switch (unit) {
    case 's': return value * 1000;
    case 'm': return value * 60 * 1000;
    case 'h': return value * 3600 * 1000;
    case 'd': return value * 24 * 3600 * 1000;
    default: return 3600 * 1000;
  }
}

function getMySQLDateTime(msFromNow) {
  const date = new Date(Date.now() + msFromNow);
  return date.toISOString().slice(0, 19).replace('T', ' ');
}

const app = express();
const port = process.env.PORT || 3002;
const jwtSecret = process.env.JWT_SECRET || "change_this_secret";
const jwtExpiresIn = process.env.JWT_EXPIRES_IN || "1h";
const revokedTokens = new Set();

const dbConfig = {
  host: process.env.DB_HOST || "localhost",
  user: process.env.DB_USER || "root",
  password: process.env.DB_PASS || "rootpass",
  database: process.env.DB_NAME || "smartcity",
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0
};

const pool = mysql.createPool(dbConfig);

app.use(express.json());

function sendResponse(res, status, code, data, message) {
  res.status(code).json({
    status,
    code,
    data,
    message,
    timestamp: new Date().toISOString(),
    service: "oauth-server"
  });
}

app.get("/", (req, res) => {
  sendResponse(res, "success", 200, null, "OAuth Server aktif");
});

app.get("/health", async (req, res) => {
  try {
    await pool.query("SELECT 1");
    sendResponse(res, "success", 200, { oauth: "healthy", db: "connected" }, "OAuth Server healthy");
  } catch (err) {
    sendResponse(res, "error", 500, { oauth: "healthy", db: "disconnected", error: err.message }, "OAuth Server DB disconnected");
  }
});

app.post("/oauth/token", async (req, res) => {
  const {
    grant_type = "password",
    username,
    password,
    client_id,
    client_secret
  } = req.body;

  let payload;
  let user_id = null;
  let user_type = "citizen";

  if (grant_type === "password") {
    if (!username || !password) {
      return sendResponse(res, "error", 400, null, "username and password are required");
    }

    if (username === "admin" && password === "password") {
      payload = {
        sub: "admin",
        role: "admin",
        scope: "smart-energy"
      };
      user_type = "admin";
    } else {
      try {
        const [rows] = await pool.query(
          "SELECT * FROM citizen_citizens WHERE email = ? OR nik = ?",
          [username, username]
        );

        if (rows.length === 0) {
          return sendResponse(res, "error", 401, null, "Invalid username or password");
        }

        const citizen = rows[0];
        if (password !== citizen.nik) {
          return sendResponse(res, "error", 401, null, "Invalid username or password");
        }

        payload = {
          sub: citizen.email,
          role: citizen.role,
          scope: citizen.role === "admin" ? "smart-energy" : "smart-energy:citizen"
        };
        user_id = citizen.id;
        user_type = citizen.role;
      } catch (err) {
        return sendResponse(res, "error", 500, null, "Database error: " + err.message);
      }
    }
  } else if (grant_type === "client_credentials") {
    if (!client_id || !client_secret) {
      return sendResponse(res, "error", 400, null, "client_id and client_secret are required");
    }

    try {
      const [rows] = await pool.query(
        "SELECT * FROM shared_oauth_clients WHERE client_id = ?",
        [client_id]
      );

      if (rows.length === 0) {
        return sendResponse(res, "error", 401, null, "Invalid client credentials");
      }

      const client = rows[0];
      const validSecret = bcrypt.compareSync(client_secret, client.client_secret);

      if (!validSecret) {
        return sendResponse(res, "error", 401, null, "Invalid client credentials");
      }

      payload = {
        sub: client_id,
        role: "service",
        scope: "internal"
      };
      user_type = "service";
    } catch (err) {
      return sendResponse(res, "error", 500, null, "Database error: " + err.message);
    }
  } else if (grant_type === "refresh_token") {
    const { refresh_token } = req.body;
    if (!refresh_token) {
      return sendResponse(res, "error", 400, null, "refresh_token is required");
    }

    try {
      const [tokenRows] = await pool.query(
        "SELECT * FROM shared_oauth_tokens WHERE refresh_token = ? AND refresh_token_expires_at > NOW()",
        [refresh_token]
      );

      if (tokenRows.length === 0) {
        return sendResponse(res, "error", 401, null, "Invalid or expired refresh token");
      }

      const tokenRecord = tokenRows[0];

      // Revoke/Delete the old refresh token record
      await pool.query("DELETE FROM shared_oauth_tokens WHERE id = ?", [tokenRecord.id]);

      user_id = tokenRecord.user_id;
      user_type = tokenRecord.user_type;

      if (user_id) {
        const [userRows] = await pool.query(
          "SELECT * FROM citizen_citizens WHERE id = ?",
          [user_id]
        );
        const user = userRows[0];
        payload = {
          sub: user ? user.email : "unknown",
          role: user ? user.role : user_type,
          scope: (user ? user.role : user_type) === "admin" ? "smart-energy" : "smart-energy:citizen"
        };
      } else {
        payload = {
          sub: tokenRecord.client_id,
          role: "service",
          scope: "internal"
        };
      }
    } catch (err) {
      return sendResponse(res, "error", 500, null, "Database error: " + err.message);
    }
  } else {
    return sendResponse(res, "error", 400, null, "Unsupported grant type");
  }

  const access_token = jwt.sign(payload, jwtSecret, { expiresIn: jwtExpiresIn });
  const refresh_token = crypto.randomBytes(40).toString("hex");

  const accessExpiresMs = parseDuration(jwtExpiresIn);
  const refreshExpiresMs = 7 * 24 * 3600 * 1000; // 7 days

  const expires_at = getMySQLDateTime(accessExpiresMs);
  const refresh_token_expires_at = getMySQLDateTime(refreshExpiresMs);

  try {
    await pool.query(
      "INSERT INTO shared_oauth_tokens (client_id, user_id, user_type, access_token, expires_at, refresh_token, refresh_token_expires_at) VALUES (?, ?, ?, ?, ?, ?, ?)",
      [
        client_id || "smart-city-client",
        user_id,
        user_type,
        access_token,
        expires_at,
        refresh_token,
        refresh_token_expires_at
      ]
    );
  } catch (err) {
    return sendResponse(res, "error", 500, null, "Failed to store token: " + err.message);
  }

  return sendResponse(res, "success", 200, {
    access_token,
    token_type: "Bearer",
    expires_in: Math.floor(accessExpiresMs / 1000),
    refresh_token
  }, "Token issued");
});

app.post("/oauth/introspect", async (req, res) => {
  const token = req.body.token;

  if (!token) {
    return sendResponse(res, "success", 200, { active: false }, "Token inactive");
  }

  try {
    const [tokenRows] = await pool.query(
      "SELECT * FROM shared_oauth_tokens WHERE access_token = ? AND expires_at > NOW()",
      [token]
    );

    if (tokenRows.length === 0) {
      return sendResponse(res, "success", 200, { active: false }, "Token inactive");
    }

    const decoded = jwt.verify(token, jwtSecret);

    return sendResponse(res, "success", 200, {
      active: true,
      sub: decoded.sub,
      role: decoded.role,
      scope: decoded.scope,
      exp: decoded.exp,
      iat: decoded.iat,
      user_id: tokenRows[0].user_id
    }, "Token active");
  } catch (error) {
    return sendResponse(res, "success", 200, { active: false }, "Token inactive");
  }
});

app.post("/oauth/revoke", async (req, res) => {
  const token = req.body.token;

  if (!token) {
    return sendResponse(res, "error", 400, null, "Token is required");
  }

  try {
    await pool.query(
      "DELETE FROM shared_oauth_tokens WHERE access_token = ? OR refresh_token = ?",
      [token, token]
    );
    return sendResponse(res, "success", 200, null, "Token revoked");
  } catch (err) {
    return sendResponse(res, "error", 500, null, "Database error: " + err.message);
  }
});

app.listen(port, () => {
  console.log(`OAuth Server berjalan pada port ${port}`);
});
