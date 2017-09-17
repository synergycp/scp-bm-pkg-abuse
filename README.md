## About

The Abuse package retrieves abuse reports via IMAP from an email account and distributes the reports to the appropriate server tenants based on server IP. It adds a frontend to SynergyCP that allows Clients and Administrators to see and comment on reports.

## Setting up

1. Install the Abuse package on SynergyCP. As root:

```bash
sudo /scp/bin/scp-package abuse
```

2. Go into the SynergyCP application and modify the Settings for Abuse to reflect the authentication details for the email account receiving abuse reports.
3. Your abuse reports will be automatically synced every 5 minutes. You can check on the status of existing abuse reports by visiting the Network > Abuse Reports page.
