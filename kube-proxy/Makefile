#overwrite this to build for different arch and operative systems
BUILDOS=linux
BUILDARCH=amd64

build:
	mkdir bin 2>/dev/null; true
	env GOOS=${BUILDOS} GOARCH=${BUILDARCH} go build -o bin/${BUILDOS}-${BUILDARCH}

update-dep:
	glide up --strip-vendor

# installs the dependencies in the glide.lock file
install-dep:
	glide install --strip-vendor