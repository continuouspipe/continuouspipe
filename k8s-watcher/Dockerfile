FROM node

COPY package.json /app/package.json
WORKDIR /app

# Install app dependencies
RUN npm install

# Bundle app source
COPY . /app

COPY ./docker/run /usr/bin/k8s-watcher
CMD ["/usr/bin/k8s-watcher"]
