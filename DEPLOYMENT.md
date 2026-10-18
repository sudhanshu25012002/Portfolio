# Auto-Deployment Guide: Hostinger Native Git Integration

This document outlines how the automatic deployment for **`sudhanshu.helioslabs.in`** is structured, how it functions under the hood, and how to replicate or troubleshoot this setup step by step.

---

## 1. How It Works

Hostinger provides a native Git integration that listens directly to GitHub repository events using secure OAuth webhooks. 

```
Local Machine (Developer)
       │
       │  git push origin main
       ▼
GitHub Repository (sudhanshu25012002/Portfolio)
       │
       │  Instant Webhook Event (push notification)
       ▼
Hostinger Server (u359647992)
       │
       │  Runs: git pull on server
       ▼
Live Subdomain Document Root
(/home/u359647992/domains/sudhanshu.helioslabs.in/public_html)
       │
       ▼
Website Live at https://sudhanshu.helioslabs.in (2-5 seconds)
```

### Why Native Git Auto-Deploy is Superior to FTP / GitHub Actions
1. **Speed:** Uses native `git pull` on the server—only modified/new files are transferred, deploying in ~2 to 5 seconds.
2. **Zero Credentials to Maintain:** No FTP usernames or passwords need to be stored in GitHub Secrets.
3. **No CI Runner Consumption:** Uses 0 GitHub Actions compute minutes.
4. **Clean File Sync:** Removes deleted files automatically when pulled, keeping the production folder clean.

---

## 2. Configuration Details for this Project

- **Subdomain:** `sudhanshu.helioslabs.in`
- **Hosting Account / User:** `u359647992`
- **Server Document Root:** `/home/u359647992/domains/sudhanshu.helioslabs.in/public_html`
- **Connected Repository:** `sudhanshu25012002/Portfolio`
- **Tracked Branch:** `main`
- **Auto-Deploy Status:** `Enabled`

---

## 3. Step-by-Step Setup Guide

If you ever need to set this up from scratch or replicate it on another domain/subdomain, follow these steps:

### Step 1: Connect your GitHub Account to Hostinger
1. Log in to [Hostinger hPanel](https://hpanel.hostinger.com/).
2. Navigate to **Websites** and click **Manage** next to your domain/subdomain.
3. In the left sidebar, navigate to **Advanced** > **Git** (or search for `Git`).
4. Click **Connect GitHub Account** and authorize Hostinger via OAuth.

### Step 2: Configure the Repository & Branch
1. Under **Deploy from Git**:
   - **Repository:** Select `sudhanshu25012002/Portfolio` from the dropdown list.
   - **Branch:** Select `main`.
   - **Install Path / Directory:** Leave blank (or enter `/`) so it deploys directly into `public_html`.
2. Check the box for **Automatic Deployment** (triggers deployment on push).
3. Click **Create** / **Deploy**.

*(Note: In this repository, this was performed programmatically using the Hostinger MCP API tool `hosting_git_update-auto-deployment-settings`)*.

---

## 4. Daily Workflow

Whenever you make changes to your portfolio:

```bash
# 1. Stage your changes
git add .

# 2. Commit with a descriptive message
git commit -m "Update portfolio projects and skills"

# 3. Push to main
git push origin main
```

Within 2 to 5 seconds, your changes are live at **`https://sudhanshu.helioslabs.in`**.

---

## 5. Verifying Deployment & Troubleshooting

### Check Live Status
You can test the site headers or content at any time:
```bash
curl -I https://sudhanshu.helioslabs.in
```

### Manual Redeploy via Hostinger hPanel
If for any reason an update is not reflected immediately:
1. Go to **hPanel** > **Websites** > **Manage** (`sudhanshu.helioslabs.in`).
2. Go to **Advanced** > **Git**.
3. Under **Manage Git Repositories**, click the **Deploy** or **Pull** button next to the repository.

### Document Root Conflicts
If you ever see an error `Project directory is not empty`:
- Git requires an empty directory or an existing clone of the same branch to pull into.
- If default files (e.g. `default.php`) exist in a fresh subdomain, remove them before the initial clone.

---

## 6. Safety & Security Boundary

Per the rules defined in `AGENTS.md` and `.agents/rules/hostinger-scope.md`:
- All automated actions, deployments, and file modifications on Hostinger are strictly confined to **`sudhanshu.helioslabs.in`**.
- No other domains or subdomains across the Hostinger account will ever be modified by automated agents.
