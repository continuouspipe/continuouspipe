FROM nginx

# Install NodeJS
RUN apt-get update \
 && apt-get install -y curl \
 && curl -sL https://deb.nodesource.com/setup_5.x | bash - \
 && apt-get install -y nodejs build-essential

# Install prerequisites build tools
RUN apt-get install -y ruby ruby-dev build-essential git \
  && gem install compass \
  && npm install -g grunt-cli bower

# Build the application
RUN mkdir /build
WORKDIR /build

# Install node dependencies
ADD package.json /build/package.json
RUN npm install

# Install bower dependencies
ADD .bowerrc /build/.bowerrc
ADD bower.json /build/bower.json
RUN bower install --config.interactive=false --allow-root

# Build the code
COPY . /build
RUN grunt build \
 && rm -rf /usr/share/nginx/html/* \
 && mv dist/* /usr/share/nginx/html

WORKDIR /usr/share/nginx/html

# Copy the runnable
COPY /docker/run.sh /run.sh
CMD ["/run.sh"]
