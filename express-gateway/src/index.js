require("dotenv").config();

const express = require("express");
const requestLogger = require("./middleware/logger");
const { globalLimiter } = require("./middleware/rateLimit");
const registerProxyRoutes = require("./routes/proxy");

const app = express();
const port = process.env.PORT || 3060;

app.use(express.json());
app.use(requestLogger);
app.use(globalLimiter);

app.get("/", (req, res) => {
  res.json({
    status: "success",
    message: "API Gateway aktif",
    service: "api-gateway"
  });
});

app.get("/health", (req, res) => {
  res.json({
    status: "success",
    code: 200,
    data: {
      gateway: "healthy"
    },
    message: "API Gateway healthy",
    service: "api-gateway"
  });
});

registerProxyRoutes(app);

app.listen(port, () => {
  console.log(`API Gateway berjalan pada port ${port}`);
});
