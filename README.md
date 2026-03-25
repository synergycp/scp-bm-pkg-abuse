## About

The Abuse package retrieves abuse reports via IMAP from an email account and distributes the reports to the appropriate server tenants based on server IP. It adds a frontend to SynergyCP that allows Clients and Administrators to see and comment on reports.

## Setting up

1. Go into the SynergyCP application and modify the Settings for Abuse to reflect the authentication details for the email account receiving abuse reports.
2. Your abuse reports will be automatically synced every 5 minutes. You can check on the status of existing abuse reports by visiting the Network > Abuse Reports page.

## Changelog

### v2.1.5
- Extract source IP from XARF JSON and XML email attachments before scraping body

### v2.1.4
- Add View Report button URL to suspension warning and suspended emails

### v2.1.3
- Fix server ID in abuse emails to use `srv_id` field instead of database primary key

### v2.1.2
- Add server ID (e.g. `srv1023`) alongside server nickname in suspension emails
- Update GitHub Actions (`actions/checkout`, `actions/setup-node`) from v4 to v5 for Node.js 24 compatibility

### v2.1.1
- Fix negative days and excessive decimal places in suspension warning emails (Carbon 3 / Laravel 11 compatibility)

### v2.1.0
- Update deprecated Laravel APIs for Laravel 11 compatibility

### v2.0.0
- Initial Laravel 11 release
