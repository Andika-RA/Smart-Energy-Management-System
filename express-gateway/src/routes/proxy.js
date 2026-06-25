const { createProxyMiddleware } = require("http-proxy-middleware");
const services = require("../config/services");
const { authMiddleware, adminMiddleware } = require("../middleware/auth");
const { authLimiter } = require("../middleware/rateLimit");

function proxyErrorHandler(err, req, res) {
  res.status(502).json({
    status: "error",
    code: 502,
    message: "Bad gateway: service is unavailable or not responding.",
    service: "api-gateway"
  });
}

const injectUserHeaders = (proxyReq, req, res) => {
  if (req.user) {
    proxyReq.setHeader("X-User-Id", req.user.user_id ? req.user.user_id.toString() : "");
    proxyReq.setHeader("X-User-Role", req.user.role || "");
    proxyReq.setHeader("X-User-Sub", req.user.sub || "");
    proxyReq.setHeader("X-Citizen-Id", req.user.user_id ? req.user.user_id.toString() : "");
  }
};

function registerProxyRoutes(app) {
  // Admin-only route: PATCH /api/reports/:id/status
  app.patch(
    "/api/reports/:id/status",
    authLimiter,
    authMiddleware,
    adminMiddleware,
    createProxyMiddleware({
      target: services.citizen,
      changeOrigin: true,
      on: {
        proxyReq: injectUserHeaders,
        error: proxyErrorHandler
      }
    })
  );

  // Generic Citizen Service Proxies
  app.use("/api/citizens", authLimiter, authMiddleware, createProxyMiddleware({ target: services.citizen, changeOrigin: true, on: { proxyReq: injectUserHeaders, error: proxyErrorHandler } }));
  app.use("/api/reports", authLimiter, authMiddleware, createProxyMiddleware({ target: services.citizen, changeOrigin: true, on: { proxyReq: injectUserHeaders, error: proxyErrorHandler } }));
  app.use("/api/notifications", authLimiter, authMiddleware, createProxyMiddleware({ target: services.citizen, changeOrigin: true, on: { proxyReq: injectUserHeaders, error: proxyErrorHandler } }));
  
  // Power & Grid Services Proxies
  app.use("/api/power", authLimiter, authMiddleware, createProxyMiddleware({ target: services.power, changeOrigin: true, on: { proxyReq: injectUserHeaders, error: proxyErrorHandler } }));
  app.use("/api/grid", authLimiter, authMiddleware, createProxyMiddleware({ target: services.grid, changeOrigin: true, on: { proxyReq: injectUserHeaders, error: proxyErrorHandler } }));
  app.use("/api/zones", authLimiter, authMiddleware, createProxyMiddleware({ target: services.grid, changeOrigin: true, on: { proxyReq: injectUserHeaders, error: proxyErrorHandler } }));
  app.use("/api/grid-readings", authLimiter, authMiddleware, createProxyMiddleware({ target: services.grid, changeOrigin: true, on: { proxyReq: injectUserHeaders, error: proxyErrorHandler } }));
  app.use("/api/grid-quality", authLimiter, authMiddleware, createProxyMiddleware({ target: services.grid, changeOrigin: true, on: { proxyReq: injectUserHeaders, error: proxyErrorHandler } }));
  app.use("/api/grid-incidents", authLimiter, authMiddleware, createProxyMiddleware({ target: services.grid, changeOrigin: true, on: { proxyReq: injectUserHeaders, error: proxyErrorHandler } }));
  
  // Machine Learning Service Proxies
  app.use("/predict", authLimiter, authMiddleware, createProxyMiddleware({ target: services.ml, changeOrigin: true, on: { proxyReq: injectUserHeaders, error: proxyErrorHandler } }));
  app.use("/detect", authLimiter, authMiddleware, createProxyMiddleware({ target: services.ml, changeOrigin: true, on: { proxyReq: injectUserHeaders, error: proxyErrorHandler } }));
  app.use("/model", authLimiter, authMiddleware, createProxyMiddleware({ target: services.ml, changeOrigin: true, on: { proxyReq: injectUserHeaders, error: proxyErrorHandler } }));
}

module.exports = registerProxyRoutes;
