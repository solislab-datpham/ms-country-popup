const { spawnSync } = require('node:child_process')
const { mkdirSync, readdirSync, renameSync, writeFileSync } = require('node:fs')
const path = require('node:path')

const root = path.resolve(__dirname, '..')
const slug = path.basename(root)
const files = readdirSync(root, { withFileTypes: true })
  .filter(entry => entry.isFile() && entry.name.endsWith('.php'))
  .map(entry => entry.name)
files.push('assets')

const archive = spawnSync(
  'zip',
  ['-q', '-X', '-r', '-', ...files.map(file => `${slug}/${file}`), '-x', '*/.*', '*.map', '*.log', '*.bak', '*.zip'],
  { cwd: path.dirname(root) }
)

if (archive.error) throw archive.error
if (archive.status !== 0) throw new Error(archive.stderr.toString().trim() || 'ZIP creation failed')

const dist = path.join(root, 'dist')
const output = path.join(dist, `${slug}.zip`)
const temporary = path.join(dist, `${slug}.${process.pid}.tmp`)
mkdirSync(dist, { recursive: true })
writeFileSync(temporary, archive.stdout)
renameSync(temporary, output)
console.log(`Bundled ${path.relative(root, output)}`)
