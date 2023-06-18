import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";
import fs from "fs";

export default defineConfig({
  plugins: [symfonyPlugin()],
  root: ".",
  base: "/build/",
  server: {
    https: {
      key: fs.readFileSync("certs/key.pem"),
      cert: fs.readFileSync("certs/cert.pem"),
    },
    host: true,
    cors: true,
    hmr: {
      host: "node.project-manager.localhost",
      protocol: "wss",
    },
  },
  build: {
    manifest: true,
    emptyOutDir: true,
    assetsDir: "",
    outDir: "./public/build",
    rollupOptions: {
      input: {
        app: "./assets/app.js",
      },
    },
  },
});
