import { spawn } from 'node:child_process';
import { readFileSync } from 'node:fs';
import http from 'node:http';
import https from 'node:https';
import path from 'node:path';
import process from 'node:process';

const host = '127.0.0.1';
const httpsPort = 8002;
const laravelPort = 8003;
const projectRoot = process.cwd();
const laragonSsl = 'C:/laragon/etc/ssl';

let laravel;

const server = https.createServer(
    {
        key: readFileSync(path.join(laragonSsl, 'laragon.key')),
        cert: readFileSync(path.join(laragonSsl, 'laragon.crt')),
    },
    (request, response) => {
        const proxy = http.request(
            {
                hostname: host,
                port: laravelPort,
                path: request.url,
                method: request.method,
                headers: {
                    ...request.headers,
                    host: `localhost:${httpsPort}`,
                    'x-forwarded-for': request.socket.remoteAddress ?? host,
                    'x-forwarded-host': `localhost:${httpsPort}`,
                    'x-forwarded-port': String(httpsPort),
                    'x-forwarded-proto': 'https',
                },
            },
            (proxyResponse) => {
                response.writeHead(proxyResponse.statusCode ?? 502, proxyResponse.headers);
                proxyResponse.pipe(response);
            },
        );

        proxy.on('error', () => {
            if (! response.headersSent) {
                response.writeHead(502, { 'content-type': 'text/plain' });
            }

            response.end('Laravel is starting. Refresh in a moment.');
        });

        request.pipe(proxy);
    },
);

server.listen(httpsPort, host, () => {
    console.log(`Secure Laravel server running at https://localhost:${httpsPort}`);

    laravel = spawn(
        'php',
        ['artisan', 'serve', `--host=${host}`, `--port=${laravelPort}`],
        { cwd: projectRoot, stdio: 'inherit', windowsHide: true },
    );

    laravel.on('exit', (code) => {
        server.close();
        process.exitCode = code ?? 0;
    });
});

server.on('error', (error) => {
    console.error(`Unable to start HTTPS server: ${error.message}`);
    process.exitCode = 1;
});

const shutdown = () => {
    server.close();
    laravel?.kill();
};

process.on('SIGINT', shutdown);
process.on('SIGTERM', shutdown);
