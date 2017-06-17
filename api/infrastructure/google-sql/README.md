# Google SQL Proxy

Google SQL Proxy sets up a Secure Tunnel from the cluster to the remote database instance, bypassing the need to connect via the
public internet and ensuring communications to the database are encrypted.

We run a group of 3 instances of the proxy in production to provide redundancy against a node going down.

There needs to be a sql proxy deployment per database server that we connect to, so for staging we can live with one per cluster but
production needs one per microservice.

## Staging Setup

1. If one hasn't been created already, create a user on Staging Google SQL instance that is allowed to connect from hostname "cloudsqlproxy~%" here: https://console.cloud.google.com/sql/instances/staging-database/access-control/users?project=continuous-pipe-1042&organizationId=1070460756235
Ensure the password you use is long and complex.
2. If one hasn't been created already, create a service account user with "Cloud SQL Client" permissions. To set this up right you must have "owner" privileges on the Google project: https://console.cloud.google.com/iam-admin/serviceaccounts/project?project=continuous-pipe-1042&organizationId=1070460756235
Be sure to click the "Furnish a new private key" checkbox and upload it to LastPass for usage later.
Double check that this user appears on the IAM page: https://console.cloud.google.com/iam-admin/iam/project?project=continuous-pipe-1042&organizationId=1070460756235
3. Run `kubectl create namespace "sql-proxy-staging"`
4. Run `kubectl --namespace=sql-proxy-staging create secret generic cloudsql-instance-credentials --from-file=credentials.json=sql-proxy-staging-service-account.json` where `sql-proxy-staging-service-account.json` was the file that
was downloaded from the user creation on the Service Accounts page.
4. Run `kubectl --namespace=sql-proxy-staging create -f google-sql-proxy.service.yml`
5. Run `kubectl --namespace=sql-proxy-staging create -f google-sql-proxy.staging.deployment.yml`
6. Update the DATABASE_HOST key of the microservice to be `google-sql-proxy.sql-proxy-staging.svc.cluster.local`


## Production Setup

1. If one hasn't been created already, create a user on Production Google SQL instance that is allowed to connect from hostname "cloudsqlproxy~%" here: https://console.cloud.google.com/sql/instances/river-prod/access-control/users?project=continuous-pipe-1042&organizationId=1070460756235
Ensure the password you use is long and complex.
2. If one hasn't been created already, create a service account user with "Cloud SQL Client" permissions. To set this up right you must have "owner" privileges on the Google project: https://console.cloud.google.com/iam-admin/serviceaccounts/project?project=continuous-pipe-1042&organizationId=1070460756235
Be sure to click the "Furnish a new private key" checkbox and upload it to LastPass for usage later.
Double check that this user appears on the IAM page: https://console.cloud.google.com/iam-admin/iam/project?project=continuous-pipe-1042&organizationId=1070460756235
3. Run `kubectl --namespace=river-production create secret generic cloudsql-instance-credentials --from-file=credentials.json=sql-proxy-production-service-account.json` where `sql-proxy-production-service-account.json` was the file that
was downloaded from the user creation on the Service Accounts page.
4. Run `kubectl --namespace=river-production create -f google-sql-proxy.service.yml`
5. Run `kubectl --namespace=river-production create -f google-sql-proxy.production.deployment.yml`
6. Update the DATABASE_HOST key of the microservice to be `google-sql-proxy`
