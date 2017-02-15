FROM golang:1.7

# Add the application
ADD . /go/src/github.com/continuouspipe/kube-proxy
WORKDIR /go/src/github.com/continuouspipe/kube-proxy

# Env variables
ENV KEY_NAME server
ENV COMMON_NAME server

# Install glide
RUN curl https://glide.sh/get | sh

# Install dependencies
RUN make install-dep

# Run build
RUN make build

# Switch to the bin folder
WORKDIR /go/src/github.com/continuouspipe/kube-proxy/bin

# Generate self signed certificate
CMD /usr/bin/openssl genrsa -out ${KEY_NAME}.key 1024 && \
    /usr/bin/openssl req  -new -newkey rsa:4096 -days 365 -nodes -subj "/C=/ST=/L=/O=/CN=${COMMON_NAME}" -keyout ${KEY_NAME}.key -out ${KEY_NAME}.csr  && \
    /usr/bin/openssl x509 -req -days 365 -in ${KEY_NAME}.csr -signkey ${KEY_NAME}.key -out ${KEY_NAME}.crt

RUN chmod +x /go/src/github.com/continuouspipe/kube-proxy/docker/run.sh
CMD ["/go/src/github.com/continuouspipe/kube-proxy/docker/run.sh"]