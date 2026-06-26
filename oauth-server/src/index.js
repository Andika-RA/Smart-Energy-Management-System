require('dotenv').config();
const express = require('express');
const mysql = require('mysql2/promise');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');

const app = express();
app.use(express.urlencoded({ extended: true })); 
app.use(express.json());

const PORT = process.env.PORT || 3002;
const JWT_SECRET = process.env.JWT_SECRET;

const dbPool = mysql.createPool({
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASS,
    database: process.env.DB_NAME,
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

const generateAccessToken = (payload) => jwt.sign(payload, JWT_SECRET, { expiresIn: '1h' });
const generateRefreshToken = (payload) => jwt.sign(payload, JWT_SECRET, { expiresIn: '7d' });

app.post('/oauth/token', async (req, res) => {
    try {
        const { grant_type, client_id, client_secret, username, password, refresh_token } = req.body;
        if (!client_id || !client_secret) {
            return res.status(401).json({ error: "invalid_client", error_description: "Missing client_id or client_secret" });
        }
        const [clients] = await dbPool.query('SELECT * FROM shared_oauth_clients WHERE client_id = ?', [client_id]);
        if (clients.length === 0) return res.status(401).json({ error: "invalid_client", error_description: "Client not found" });
        const client = clients[0];
        const isClientValid = await bcrypt.compare(client_secret, client.client_secret);
        if (!isClientValid) return res.status(401).json({ error: "invalid_client", error_description: "Invalid client_secret" });
        if (!client.grant_types.includes(grant_type)) {
            return res.status(400).json({ error: "unsupported_grant_type", error_description: `Client is not allowed to use ${grant_type}` });
        }
        let accessToken, refreshToken, expiresAt;
        let userId = null;
        let userType = 'service';
        if (grant_type === 'password') {
            if (!username || !password) return res.status(400).json({ error: "invalid_request", error_description: "Missing username or password" });
            const [users] = await dbPool.query('SELECT * FROM citizen_citizens WHERE email = ?', [username]);
            if (users.length === 0) return res.status(400).json({ error: "invalid_grant", error_description: "Invalid credentials" });
            const user = users[0];
            const isMatch = await bcrypt.compare(password, user.password);
            if (!isMatch) return res.status(400).json({ error: "invalid_grant", error_description: "Invalid credentials" });
            userId = user.id;
            userType = user.role === 'admin' ? 'admin' : 'citizen';
            const payload = { sub: user.id, email: user.email, role: user.role, type: userType, client_id };
            accessToken = generateAccessToken(payload);
            refreshToken = generateRefreshToken(payload);
        } else if (grant_type === 'client_credentials') {
            const payload = { sub: client_id, type: 'service', client_id };
            accessToken = generateAccessToken(payload);
            userType = 'service';
        } else if (grant_type === 'refresh_token') {
            if (!refresh_token) return res.status(400).json({ error: "invalid_request", error_description: "Missing refresh_token parameter" });
            let decoded;
            try { decoded = jwt.verify(refresh_token, JWT_SECRET); } 
            catch (e) { return res.status(400).json({ error: "invalid_grant", error_description: "Refresh token expired or invalid" }); }
            const [tokens] = await dbPool.query('SELECT * FROM shared_oauth_tokens WHERE refresh_token = ? AND client_id = ?', [refresh_token, client_id]);
            if (tokens.length === 0) return res.status(400).json({ error: "invalid_grant", error_description: "Token has been revoked or not found" });
            userId = decoded.sub;
            userType = decoded.type;
            const payload = { sub: userId, email: decoded.email, role: decoded.role, type: userType, client_id };
            accessToken = generateAccessToken(payload);
            refreshToken = generateRefreshToken(payload);
            await dbPool.query('DELETE FROM shared_oauth_tokens WHERE refresh_token = ?', [refresh_token]);
        } else {
            return res.status(400).json({ error: "unsupported_grant_type" });
        }
        expiresAt = new Date(Date.now() + 3600000);
        await dbPool.query(
            'INSERT INTO shared_oauth_tokens (client_id, user_id, user_type, access_token, refresh_token, expires_at) VALUES (?, ?, ?, ?, ?, ?)',
            [client_id, userId, userType, accessToken, refreshToken || null, expiresAt]
        );
        res.json({
            access_token: accessToken,
            token_type: "Bearer",
            expires_in: 3600,
            refresh_token: refreshToken
        });
    } catch (error) {
        console.error(error);
        res.status(500).json({ error: "server_error" });
    }
});

app.post('/oauth/introspect', async (req, res) => {
    const { token } = req.body;
    if (!token) return res.status(400).json({ active: false });
    try {
        const decoded = jwt.verify(token, JWT_SECRET);
        const [tokens] = await dbPool.query('SELECT id FROM shared_oauth_tokens WHERE access_token = ?', [token]);
        if (tokens.length === 0) return res.json({ active: false });
        res.json({
            active: true,
            client_id: decoded.client_id,
            sub: decoded.sub,
            exp: decoded.exp,
            type: decoded.type,
            role: decoded.role
        });
    } catch (error) {
        res.json({ active: false });
    }
});

app.post('/oauth/revoke', async (req, res) => {
    const { token } = req.body;
    if (!token) return res.status(400).json({ error: "invalid_request" });
    await dbPool.query('DELETE FROM shared_oauth_tokens WHERE access_token = ? OR refresh_token = ?', [token, token]);
    res.status(200).json({});
});

app.listen(PORT, () => {
    console.log(`Strict OAuth 2.0 Server running on port ${PORT}`);
});