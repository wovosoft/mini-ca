import * as fs from 'fs';
import * as https from 'https';

const options = {
    key: fs.readFileSync('ssls/localhost_key.pem'), // leaf private key
    cert: fs.readFileSync('ssls/localhost_cert.pem'), // leaf cert
};

https
    .createServer(options, (req, res) => {
        res.writeHead(200);
        res.end('Hello from your self-signed TLS server!\n');
    })
    .listen(8443, () => {
        console.log('Server running at https://localhost:8443');
    });
