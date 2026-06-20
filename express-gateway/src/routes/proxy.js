const { createProxyMiddleware } = require("http-proxy-middleware");
const services = require("../config/services");

function proxyErrorHandler(err, req, res) {
  res.status(502).json({
    status: "error",
    code: 502,
    message: "Bad gateway: service is unavailable or not responding.",
    service: "api-gateway"
  });
}

function registerProxyRoutes(app) {
  app.use("/api/citizens", createProxyMiddleware({ target: services.citizen, changeOrigin: true, on: { error: proxyErrorHandler } }));
  app.use("/api/power", createProxyMiddleware({ target: services.power, changeOrigin: true, on: { error: proxyErrorHandler } }));
  app.use("/api/grid", createProxyMiddleware({ target: services.grid, changeOrigin: true, on: { error: proxyErrorHandler } }));
  app.use("/predict", createProxyMiddleware({ target: services.ml, changeOrigin: true, on: { error: proxyErrorHandler } }));
  app.use("/detect", createProxyMiddleware({ target: services.ml, changeOrigin: true, on: { error: proxyErrorHandler } }));
}

module.exports = registerProxyRoutes;
