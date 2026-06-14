require("dotenv").config();

const express = require("express");
const requestLogger = require("./middleware/logger");

const app = express();
const port = process.env.PORT || 3060;

app.use(express.json());
app.use(requestLogger);

app.get("/", (req, res) => {
  res.json({
    status: "success",
    message: "API Gateway aktif",
    service: "api-gateway"
  });
});

app.listen(port, () => {
  console.log(`API Gateway berjalan pada port ${port}`);
});
