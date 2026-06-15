const services = {
  citizen: process.env.CITIZEN_SERVICE_URL || "http://localhost:8000",
  power: process.env.POWER_SERVICE_URL || "http://localhost:8001",
  grid: process.env.GRID_SERVICE_URL || "http://localhost:8002",
  ml: process.env.PYTHON_ML_URL || "http://localhost:5000"
};

module.exports = services;
