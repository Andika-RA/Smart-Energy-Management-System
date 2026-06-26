require("dotenv").config();

const express = require("express");
const jwt = require("jsonwebtoken");

const app = express();
const port = process.env.PORT || 3002;
const jwtSecret = process.env.JWT_SECRET || "change_this_secret";
const jwtExpiresIn = process.env.JWT_EXPIRES_IN || "1h";
const revokedTokens = new Set();

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

app.get("/health", (req, res) => {
  sendResponse(res, "success", 200, { oauth: "healthy" }, "OAuth Server healthy");
});

app.post("/oauth/token", (req, res) => {
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
    const validClient = client_id === (process.env.OAUTH_CLIENT_ID || "smart-energy-client");
    const validSecret = client_secret === (process.env.OAUTH_CLIENT_SECRET || "smart-energy-secret");

    if (!validClient || !validSecret) {
      return sendResponse(res, "error", 401, null, "Invalid client credentials");
    }

    payload = {
      sub: client_id,
      role: "service",
      scope: "internal"
    };
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
