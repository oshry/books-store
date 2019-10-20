FROM silintl/php7:latest
MAINTAINER Oshry Levy <oshrylevy@gmail.com>

RUN apt-get update && apt-get install -y \
    php7.0-gd \
    vim \
 && rm -rf /var/lib/apt/lists/* \
 && rm /etc/apache2/sites-enabled/vhost.conf
# Copy an Apache vhost file into sites-enabled. This should map
# the document root to whatever is right for your app
COPY vhost-config.conf /etc/apache2/sites-enabled/

RUN ln -sf /proc/self/fd/1 /var/log/apache2/access.log && \
    ln -sf /proc/self/fd/1 /var/log/apache2/error.log
#RUN ln -sf /dev/stdout /var/log/apache2/access.log \
#    && ln -sf /dev/stderr /var/log/apache2/error.log
VOLUME ["/var/www/html"]
#COPY . /var/www/html/ <-- you need this if you want to deploy a container in production

COPY docker-entry.sh /
RUN chmod +x /docker-entry.sh

ENTRYPOINT ["/docker-entry.sh"]