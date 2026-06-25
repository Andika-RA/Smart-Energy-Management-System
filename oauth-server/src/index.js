require("dotenv").config();

const express = require("express");
const jwt = require("jsonwebtoken");
const mysql = require("mysql2/promise");
const bcrypt = require("bcryptjs");

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

  if (grant_type === "password") {
    const validUser = username === (process.env.OAUTH_DEMO_USERNAME || "admin");
    const validPassword = password === (process.env.OAUTH_DEMO_PASSWORD || "password");

    if (!validUser || !validPassword) {
      return sendResponse(res, "error", 401, null, "Invalid username or password");
    }

    payload = {
      sub: username,
      role: "admin",
      scope: "smart-energy"
    };
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
    } catch (err) {
      return sendResponse(res, "error", 500, null, "Database error: " + err.message);
    }
  } else {
    return sendResponse(res, "error", 400, null, "Unsupported grant type");
  }

  const token = jwt.sign(payload, jwtSecret, { expiresIn: jwtExpiresIn });

  return sendResponse(res, "success", 200, {
    access_token: token,
    token_type: "Bearer",
    expires_in: jwtExpiresIn
  }, "Token issued");
});

app.post("/oauth/introspect", (req, res) => {
  const token = req.body.token;

  if (!token || revokedTokens.has(token)) {
    return sendResponse(res, "success", 200, { active: false }, "Token inactive");
  }

  try {
    const decoded = jwt.verify(token, jwtSecret);

    return sendResponse(res, "success", 200, {
      active: true,
      sub: decoded.sub,
      role: decoded.role,
      scope: decoded.scope,
      exp: decoded.exp,
      iat: decoded.iat
    }, "Token active");
  } catch (error) {
    return sendResponse(res, "success", 200, { active: false }, "Token inactive");
  }
});

app.post("/oauth/revoke", (req, res) => {
  const token = req.body.token;

  if (!token) {
    return sendResponse(res, "error", 400, null, "Token is required");
  }

  revokedTokens.add(token);
  return sendResponse(res, "success", 200, null, "Token revoked");
});

app.listen(port, () => {
  console.log(`OAuth Server berjalan pada port ${port}`);
});
