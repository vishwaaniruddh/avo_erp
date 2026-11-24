const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const rootDir = path.resolve(__dirname, '..');
const clientDir = path.join(rootDir, 'client');
const distDir = path.join(clientDir, 'dist');
const skipBuild = process.argv.includes('--skip-build');

console.log('====================================================');
console.log('🚀 AVO ERP: Building Client & Deploying to Root');
console.log('====================================================');

// Step 1: Run Vite build if not skipped
if (!skipBuild) {
  console.log('\n📦 Step 1: Running client build (Vite + TypeScript)...');
  try {
    execSync('npm run build', {
      cwd: clientDir,
      stdio: 'inherit',
    });
    console.log('✅ Client build completed successfully.');
  } catch (err) {
    console.error('❌ Build failed:', err.message);
    process.exit(1);
  }
} else {
  console.log('\n⏩ Skipping build step (--skip-build flag provided).');
}

// Check if dist directory exists
if (!fs.existsSync(distDir)) {
  console.error(`❌ Dist directory not found at: ${distDir}`);
  process.exit(1);
}

// Helper: recursive copy
function copyRecursiveSync(src, dest) {
  const exists = fs.existsSync(src);
  const stats = exists && fs.statSync(src);
  const isDirectory = exists && stats.isDirectory();

  if (isDirectory) {
    if (!fs.existsSync(dest)) {
      fs.mkdirSync(dest, { recursive: true });
    }
    fs.readdirSync(src).forEach((childItemName) => {
      copyRecursiveSync(path.join(src, childItemName), path.join(dest, childItemName));
    });
  } else {
    fs.copyFileSync(src, dest);
  }
}

// Step 2: Clean and Copy Assets to Root
console.log('\n📂 Step 2: Copying build artifacts to project root...');

const rootAssetsDir = path.join(rootDir, 'assets');
const distAssetsDir = path.join(distDir, 'assets');

// Remove old assets directory in root if it exists
if (fs.existsSync(rootAssetsDir)) {
  console.log('🧹 Cleaning existing root assets/ directory...');
  fs.rmSync(rootAssetsDir, { recursive: true, force: true });
}

// Copy dist/assets to root assets/
if (fs.existsSync(distAssetsDir)) {
  copyRecursiveSync(distAssetsDir, rootAssetsDir);
  const assetCount = fs.readdirSync(rootAssetsDir).length;
  console.log(`✅ Copied assets/ (${assetCount} files) -> ${rootAssetsDir}`);
}

// Step 3: Copy index.html and any other root-level dist files
const distFiles = fs.readdirSync(distDir);
let copiedCount = 0;

distFiles.forEach((file) => {
  if (file === 'assets') return; // already handled
  const srcFile = path.join(distDir, file);
  const destFile = path.join(rootDir, file);

  if (fs.statSync(srcFile).isFile()) {
    fs.copyFileSync(srcFile, destFile);
    console.log(`✅ Copied ${file} -> ${destFile}`);
    copiedCount++;
  }
});

console.log('\n====================================================');
console.log(`🎉 Deployment Ready!`);
console.log(`   - Frontend build artifacts copied to root.`);
console.log(`   - Index and assets are ready to commit or serve immediately.`);
console.log('====================================================\n');
