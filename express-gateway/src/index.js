require("dotenv").config();

const express = require("express");
const requestLogger = require("./middleware/logger");
const { globalLimiter } = require("./middleware/rateLimit");
const registerProxyRoutes = require("./routes/proxy");
const { checkUpstreamServices } = require("./utils/healthCheck");

const app = express();
const port = process.env.PORT || 3060;

app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(requestLogger);
app.use(globalLimiter);

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

registerProxyRoutes(app);

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
