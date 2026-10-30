const http = require('http');
const fs = require('fs');
const path = require('path');

const pages = ['index.html', 'about.html', 'services.html', 'portfolio.html', 'team.html', 'ai-lab.html', 'contact.html'];
const viewports = [320, 375, 390, 430, 768, 820, 834, 1024, 1280, 1440];

// Simple HTTP server for testing
const server = http.createServer((req, res) => {
  let filePath = '.' + req.url;
  if (filePath === './') filePath = './index.html';

  const extname = String(path.extname(filePath)).toLowerCase();
  const mimeTypes = {
    '.html': 'text/html',
    '.js': 'text/javascript',
    '.css': 'text/css',
    '.json': 'application/json',
    '.png': 'image/png',
    '.jpg': 'image/jpg',
    '.gif': 'image/gif',
    '.svg': 'image/svg+xml',
    '.ico': 'image/x-icon'
  };

  const contentType = mimeTypes[extname] || 'application/octet-stream';

  fs.readFile(filePath, (error, content) => {
    if (error) {
      if (error.code == 'ENOENT') {
        res.writeHead(404);
        res.end('File not found');
      } else {
        res.writeHead(500);
        res.end('Server error: ' + error.code);
      }
    } else {
      res.writeHead(200, { 'Content-Type': contentType });
      res.end(content, 'utf-8');
    }
  });
});

const PORT = 8081;
server.listen(PORT, () => {
  console.log(`\n========================================`);
  console.log(`FaraTech Viewport Verification`);
  console.log(`========================================`);
  console.log(`Server running at http://localhost:${PORT}`);
  console.log(`\nTo verify manually:`);
  console.log(`1. Open http://localhost:${PORT}/verify-viewports.html in your browser`);
  console.log(`2. Use the dropdown to select each page`);
  console.log(`3. Click "Check Horizontal Scroll" for each viewport`);
  console.log(`4. Verify the hamburger menu appears at <=1024px`);
  console.log(`5. Verify nav links are centred at >=1280px`);
  console.log(`6. Verify AI Lab hero is centred at <=1024px`);
  console.log(`7. Verify .eyebrow labels are centred on non-Home pages`);
  console.log(`\nViewports to check: ${viewports.join(', ')}px`);
  console.log(`Pages to check: ${pages.join(', ')}`);
  console.log(`\nPress Ctrl+C to stop the server\n`);
});
