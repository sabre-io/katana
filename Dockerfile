FROM debian:jessie

MAINTAINER "Diego Marangoni" <diegomarangoni@me.com>

RUN apt-get update
RUN apt-get install -y curl
RUN apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 0x5a16e7281be7a449
RUN echo deb http://dl.hhvm.com/debian jessie main | tee /etc/apt/sources.list.d/hhvm.list
RUN curl -sL https://deb.nodesource.com/setup_iojs_3.x | bash -
RUN echo '{ "allow_root": true }' > /root/.bowerrc
RUN apt-get clean && \
    apt-get update && \
    apt-get install -y sqlite hhvm build-essential git iojs && \
    rm -rf /var/lib/apt/lists/*

RUN npm install -g bower
RUN rm -rf /etc/hhvm
RUN git clone https://github.com/fruux/sabre-katana /etc/hhvm
RUN curl -sS https://getcomposer.org/installer | hhvm --php -dHttp.SlowQueryThreshold=30000
RUN mv composer.phar /usr/local/bin/composer
ENV alias composer="hhvm -v ResourceLimit.SocketDefaultTimeout=30 -v Http.SlowQueryThreshold=30000 -v Eval.Jit=false /usr/local/bin/composer"


WORKDIR /etc/hhvm
RUN make

CMD ["hhvm", "-v Http.SlowQueryThreshold=30000", "-v Eval.Jit=false", "-v ResourceLimit.SocketDefaultTimeout=30"]
