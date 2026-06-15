const { createProxyMiddleware } = require("http-proxy-middleware");
const services = require("../config/services");

function registerProxyRoutes(app) {
  app.use("/api/citizens", createProxyMiddleware({ target: services.citizen, changeOrigin: true }));
  app.use("/api/power", createProxyMiddleware({ target: services.power, changeOrigin: true }));
  app.use("/api/grid", createProxyMiddleware({ target: services.grid, changeOrigin: true }));
  app.use("/predict", createProxyMiddleware({ target: services.ml, changeOrigin: true }));
  app.use("/detect", createProxyMiddleware({ target: services.ml, changeOrigin: true }));
}

module.exports = registerProxyRoutes;
