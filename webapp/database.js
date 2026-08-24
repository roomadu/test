const mysql = require('mysql2');

// Create the connection pool. Reads from environment variables so the same
// code works locally (XAMPP/WAMP defaults) and on Hostinger (set these in
// hPanel -> Node.js app -> Environment Variables).
const pool = mysql.createPool({
    host: process.env.DB_HOST || 'localhost',
    port: process.env.DB_PORT || 3306,
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASS || '',
    database: process.env.DB_NAME || 'visitor_db',
    waitForConnections: true,
    connectionLimit: 50,
    queueLimit: 0
});

const promisePool = pool.promise();

// Create tables if they don't exist
const createTables = async () => {
    try {
        await promisePool.query(`
            CREATE TABLE IF NOT EXISTS visits (
                id INT AUTO_INCREMENT PRIMARY KEY,
                visitorName VARCHAR(255) NOT NULL,
                visitorEmail VARCHAR(255) DEFAULT '',
                visitorPhone VARCHAR(50) DEFAULT '',
                organization VARCHAR(255),
                whomToMeet VARCHAR(255) NOT NULL,
                date VARCHAR(50) NOT NULL,
                purpose VARCHAR(255),
                numPeople INT,
                status VARCHAR(50) DEFAULT 'Pending',
                meetingTime VARCHAR(100),
                note TEXT,
                createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        `);
        try {
            await promisePool.query('ALTER TABLE visits ADD COLUMN note TEXT');
        } catch (_) { /* column may already exist */ }
        console.log('Connected to MySQL and verified tables.');
    } catch (error) {
        console.error('Database connection or creation failed. Did you create "visitor_db" in phpMyAdmin?', error.message);
    }
};

createTables();

module.exports = promisePool;
