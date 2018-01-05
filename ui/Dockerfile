FROM quay.io/continuouspipe/nodejs7:stable as build

# Install prerequisites build tools
RUN apt-get update \
  && apt-get install -y ruby ruby-dev build-essential git \
  && gem install --no-rdoc --no-ri sass -v 3.4.22 \
  && gem install --no-rdoc --no-ri compass \
  && npm install -g grunt-cli bower

# Build the application
WORKDIR /app

# Install node dependencies
ADD package.json /app/package.json
RUN npm install

# Install bower dependencies
ADD .bowerrc /app/.bowerrc
ADD bower.json /app/bower.json
RUN bower install --config.interactive=false --allow-root

# Build the code
COPY . /app
RUN grunt build

FROM nginx

# Copy configuration
COPY /docker/run.sh /run.sh
COPY /docker/nginx/vhost.conf /etc/nginx/conf.d/default.conf

# Copy code
WORKDIR /app/dist
COPY --from=build /app/dist /app/dist

CMD ["/run.sh"]
