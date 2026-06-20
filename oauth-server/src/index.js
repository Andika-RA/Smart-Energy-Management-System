require("dotenv").config();

const express = require("express");

const app = express();
const port = process.env.PORT || 3002;

app.use(express.json());

app.get("/", (req, res) => {
  res.json({
    status: "success",
    message: "OAuth Server aktif",
    service: "oauth-server"
  });
});

app.get("/health", (req, res) => {
  res.json({
    status: "success",
    code: 200,
    data: {
      oauth: "healthy"
    },
    message: "OAuth Server healthy",
    service: "oauth-server"
  });
});

app.listen(port, () => {
  console.log(`OAuth Server berjalan pada port ${port}`);
});
