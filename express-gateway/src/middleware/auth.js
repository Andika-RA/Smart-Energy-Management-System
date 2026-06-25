const services = require("../config/services");
const jwt = require("jsonwebtoken");

const jwtSecret = process.env.JWT_SECRET || "change_this_secret";

function getBearerToken(req) {
  const header = req.headers.authorization || "";

  if (!header.startsWith("Bearer ")) {
    return null;
  }

  return header.slice(7);
}

async function authMiddleware(req, res, next) {
  const token = getBearerToken(req);

  if (!token) {
    return res.status(401).json({
      status: "error",
      code: 401,
      message: "Unauthorized",
      service: "api-gateway"
    });
  }

  try {
    jwt.verify(token, jwtSecret);
  } catch (error) {
    return res.status(401).json({
      status: "error",
      code: 401,
      message: "Unauthorized",
      service: "api-gateway"
    });
  }

  try {
    const response = await fetch(`${services.oauth}/oauth/introspect`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ token })
    });

    const result = await response.json();

    if (!result.data || result.data.active !== true) {
      return res.status(401).json({
        status: "error",
        code: 401,
        message: "Unauthorized",
        service: "api-gateway"
      });
    }

    req.user = result.data;
    next();
  } catch (error) {
    return res.status(503).json({
      status: "error",
      code: 503,
      message: "OAuth service unavailable",
      service: "api-gateway"
    });
  }
}

function adminMiddleware(req, res, next) {
  if (!req.user || req.user.role !== "admin") {
    return res.status(403).json({
      status: "error",
      code: 403,
      message: "Forbidden: Access denied. Admin role required.",
      service: "api-gateway"
    });
  }
  next();
}

module.exports = { authMiddleware, adminMiddleware };
