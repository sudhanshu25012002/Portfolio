# Hostinger Operations Scope & Safety Rules

## 🔒 Scope Constraint: Single Subdomain Only

When operating on Hostinger infrastructure or using Hostinger MCP tools for this project, you must adhere strictly to the following boundaries:

### Permitted Target
- **Allowed Subdomain:** `sudhanshu.helioslabs.in`
- **Allowed Document Root:** `/home/u359647992/domains/sudhanshu.helioslabs.in/public_html`
- **Allowed Repository:** `sudhanshu25012002/Portfolio` (branch: `main`)

---

### 🚫 Strictly Prohibited Actions

1. **NO Interaction with Other Domains or Subdomains:**
   - **NEVER** modify, read, deploy to, create, or delete files, DNS records, SSL certificates, databases, or settings for any other domain or subdomain on this Hostinger account.
   - Forbidden targets include, but are not limited to:
     - `helioslabs.in`
     - `reviv.helioslabs.in`
     - `vistaaram.helioslabs.in`
     - `shebakes.in`
     - `rkmetal.in` / `rkmetal.helioslabs.in`
     - `managerspizza.online` / `managerspizza.helioslabs.in`
     - `shreenarayanvidyasthal.in` / `snv.helioslabs.in`
     - Or any future client/internal domain on client ID `1018037969`.

2. **Parameter Validation on All Hostinger Tools:**
   - Every Hostinger MCP tool call (`hostinger-hosting`, `hostinger-dns`, `hostinger-domains`, `hostinger-vps`, `hostinger-wordpress`, `hostinger-billing`, `hostinger-ecommerce`) must verify that `domain` is explicitly set to `sudhanshu.helioslabs.in`.
   - If a tool does not allow restricting to `sudhanshu.helioslabs.in`, or if it operates globally across the account, do **not** execute any destructive, update, or write actions.

3. **Protection of Production Data:**
   - Never run global resets, wildcard deletions, or account-wide updates.
