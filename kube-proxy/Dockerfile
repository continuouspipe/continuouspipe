FROM golang:1.7

# Add the application
ADD . /go/src/github.com/continuouspipe/kube-proxy
WORKDIR /go/src/github.com/continuouspipe/kube-proxy

# Install glide
RUN curl https://glide.sh/get | sh

# Install dependencies
RUN glide install --strip-vendor

# Run build
RUN go install

WORKDIR /go/bin/

# Generate self signed certificate
RUN openssl genrsa -out /go/bin/server.key 1024
RUN openssl req  -new -newkey rsa:4096 -days 365 -nodes -subj "/C=/ST=/L=/O=/CN=server" -keyout /go/bin/server.key -out /go/bin/server.csr
RUN openssl x509 -req -days 365 -in /go/bin/server.csr -signkey /go/bin/server.key -out /go/bin/server.crt

# Run the outyet command by default when the container starts.
ENTRYPOINT ["/go/bin/kube-proxy", "--insecure"]
EXPOSE 8080