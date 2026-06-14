const rateLimit = require("express-rate-limit");

// Global rate limit per IP
const globalLimiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 menit
  max: 100,
  standardHeaders: true,
  legacyHeaders: false,
  message: {
    status: "error",
    code: 429,
    message: "Too many requests"
  }
});

// Rate limit untuk user yang sudah punya token
const authLimiter = rateLimit({
  windowMs: 60 * 60 * 1000, // 1 jam
  max: 500,
  keyGenerator: (req) => req.headers.authorization || req.ip
});

module.exports = { globalLimiter, authLimiter };
