const http = require('node:http');
const fs = require('node:fs');
const path = require('node:path');

const root = __dirname;
const backendHost = process.env.API_HOST || '127.0.0.1';
const backendPort = Number(process.env.API_PORT || 8000);
const port = Number(process.env.PORT || 3000);
const mimeTypes = {
  '.html': 'text/html; charset=utf-8',
  '.css': 'text/css; charset=utf-8',
  '.js': 'text/javascript; charset=utf-8',
  '.mjs': 'text/javascript; charset=utf-8',
  '.json': 'application/json; charset=utf-8',
  '.svg': 'image/svg+xml',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.webp': 'image/webp',
  '.ico': 'image/x-icon',
  '.xml': 'application/xml; charset=utf-8',
  '.txt': 'text/plain; charset=utf-8',
};

function shouldProxy(pathname) {
  return pathname === '/uploads.php' || pathname.startsWith('/api/') || pathname.startsWith('/admin/');
}

function proxyRequest(req, res) {
  const headers = { ...req.headers, host: backendHost + ':' + backendPort };
  delete headers['accept-encoding'];
  const upstream = http.request({
    hostname: backendHost,
    port: backendPort,
    method: req.method,
    path: req.url,
    headers,
  }, function (upstreamResponse) {
    res.writeHead(upstreamResponse.statusCode || 502, upstreamResponse.headers);
    upstreamResponse.pipe(res);
  });

  upstream.on('error', function (error) {
    console.error('[dev-server] Backend proxy error:', error.message);
    if (!res.headersSent) {
      res.writeHead(502, { 'Content-Type': 'application/json; charset=utf-8', 'Cache-Control': 'no-store' });
    }
    res.end(JSON.stringify({ success: false, message: 'Backend API is not running. Start PHP on port ' + backendPort + '.' }));
  });
  req.pipe(upstream);
}

const server = http.createServer(function (req, res) {
  const url = new URL(req.url, 'http://localhost');
  if (shouldProxy(url.pathname)) {
    proxyRequest(req, res);
    return;
  }

  let decodedPath;
  try {
    decodedPath = decodeURIComponent(url.pathname);
  } catch (error) {
    res.writeHead(400, { 'Content-Type': 'text/plain; charset=utf-8' });
    res.end('Bad request');
    return;
  }

  const requestedPath = decodedPath === '/' ? '/index.html' : decodedPath;
  const filePath = path.resolve(root, '.' + requestedPath);
  if (filePath !== root && !filePath.startsWith(root + path.sep)) {
    res.writeHead(403, { 'Content-Type': 'text/plain; charset=utf-8' });
    res.end('Forbidden');
    return;
  }

  fs.stat(filePath, function (error, stat) {
    if (error || !stat.isFile()) {
      res.writeHead(404, { 'Content-Type': 'text/plain; charset=utf-8', 'Cache-Control': 'no-store' });
      res.end('Not found');
      return;
    }
    res.writeHead(200, {
      'Content-Type': mimeTypes[path.extname(filePath).toLowerCase()] || 'application/octet-stream',
      'Content-Length': stat.size,
      'Cache-Control': 'no-store',
      'X-Content-Type-Options': 'nosniff',
    });
    if (req.method === 'HEAD') {
      res.end();
      return;
    }
    fs.createReadStream(filePath).pipe(res);
  });
});

server.listen(port, '0.0.0.0', function () {
  console.log('[dev-server] FaraTech frontend on http://0.0.0.0:' + port);
  console.log('[dev-server] Same-origin /api, /admin and /uploads.php requests proxy to ' + backendHost + ':' + backendPort);
});
