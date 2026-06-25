const { createProxyMiddleware } = require("http-proxy-middleware");
const services = require("../config/services");
const authMiddleware = require("../middleware/auth");
const { authLimiter } = require("../middleware/rateLimit");

function proxyErrorHandler(err, req, res) {
  res.status(502).json({
    status: "error",
    code: 502,
    message: "Bad gateway: service is unavailable or not responding.",
    service: "api-gateway"
  });
}

function registerProxyRoutes(app) {
  app.use("/api/citizens", authLimiter, authMiddleware, createProxyMiddleware({ target: services.citizen, changeOrigin: true, on: { error: proxyErrorHandler } }));
  app.use("/api/reports", authLimiter, authMiddleware, createProxyMiddleware({ target: services.citizen, changeOrigin: true, on: { error: proxyErrorHandler } }));
  app.use("/api/notifications", authLimiter, authMiddleware, createProxyMiddleware({ target: services.citizen, changeOrigin: true, on: { error: proxyErrorHandler } }));
  app.use("/api/power", authLimiter, authMiddleware, createProxyMiddleware({ target: services.power, changeOrigin: true, on: { error: proxyErrorHandler } }));
  app.use("/api/grid", authLimiter, authMiddleware, createProxyMiddleware({ target: services.grid, changeOrigin: true, on: { error: proxyErrorHandler } }));
  app.use("/api/zones", authLimiter, authMiddleware, createProxyMiddleware({ target: services.grid, changeOrigin: true, on: { error: proxyErrorHandler } }));
  app.use("/api/grid-readings", authLimiter, authMiddleware, createProxyMiddleware({ target: services.grid, changeOrigin: true, on: { error: proxyErrorHandler } }));
  app.use("/api/grid-quality", authLimiter, authMiddleware, createProxyMiddleware({ target: services.grid, changeOrigin: true, on: { error: proxyErrorHandler } }));
  app.use("/api/grid-incidents", authLimiter, authMiddleware, createProxyMiddleware({ target: services.grid, changeOrigin: true, on: { error: proxyErrorHandler } }));
  app.use("/predict", authLimiter, authMiddleware, createProxyMiddleware({ target: services.ml, changeOrigin: true, on: { error: proxyErrorHandler } }));
  app.use("/detect", authLimiter, authMiddleware, createProxyMiddleware({ target: services.ml, changeOrigin: true, on: { error: proxyErrorHandler } }));
}

module.exports = registerProxyRoutes;
