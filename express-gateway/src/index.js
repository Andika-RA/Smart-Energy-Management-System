require("dotenv").config();

const express = require("express");
const requestLogger = require("./middleware/logger");
const { globalLimiter } = require("./middleware/rateLimit");
const registerProxyRoutes = require("./routes/proxy");
const { checkUpstreamServices } = require("./utils/healthCheck");
const client = require('prom-client');

const app = express();
const port = process.env.PORT || 3060;

app.use(requestLogger);
app.use(globalLimiter);

client.collectDefaultMetrics();
const httpRequestsTotal = new client.Counter({
  name: 'http_requests_total',
  help: 'Total HTTP Requests',
  labelNames: ['job']
});

app.get('/metrics', async (req, res) => {
  res.set('Content-Type', client.register.contentType);
  res.send(await client.register.metrics());
});

app.use((req, res, next) => {
  if (req.path !== '/metrics' && req.path !== '/health') {
    httpRequestsTotal.labels('api-gateway').inc();
  }
  next();
});

registerProxyRoutes(app);

app.use(express.json());
app.use(express.urlencoded({ extended: true }));

app.get("/", (req, res) => {
  res.json({
    status: "success",
    message: "API Gateway aktif",
    service: "api-gateway"
  });
});

app.get("/health", async (req, res) => {
  const upstream = await checkUpstreamServices();

  res.json({
    status: "success",
    code: 200,
    data: {
      gateway: {
        status: "healthy",
        code: 200
      },
      upstream
    },
    message: "API Gateway healthy",
    service: "api-gateway"
  });
});

app.use((req, res) => {
  res.status(404).json({
    status: "error",
    code: 404,
    message: "Endpoint not found",
    service: "api-gateway"
  });
});

app.listen(port, () => {
  console.log(`API Gateway berjalan pada port ${port}`);
});