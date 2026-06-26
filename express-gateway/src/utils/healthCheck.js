const services = require("../config/services");

async function checkService(name, baseUrl) {
  const controller = new AbortController();
  const timeout = setTimeout(() => controller.abort(), 2000);

  try {
    const response = await fetch(`${baseUrl}/health`, {
      signal: controller.signal
    });

    return {
      name,
      status: response.ok ? "healthy" : "down",
      code: response.status
    };
  } catch (error) {
    return {
      name,
      status: "down",
      code: null
    };
  } finally {
    clearTimeout(timeout);
  }
}

async function checkUpstreamServices() {
  const checks = await Promise.all([
    checkService("oauth", services.oauth),
    checkService("citizen", services.citizen),
    checkService("power", services.power),
    checkService("grid", services.grid),
    checkService("ml", services.ml)
  ]);

  return checks.reduce((result, item) => {
    result[item.name] = {
      status: item.status,
      code: item.code
    };

    return result;
  }, {});
}

module.exports = { checkUpstreamServices };
