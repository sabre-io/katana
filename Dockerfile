FROM debian:jessie

MAINTAINER "Diego Marangoni" <diegomarangoni@me.com>

RUN apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 0x5a16e7281be7a449
RUN echo deb http://dl.hhvm.com/debian jessie main | tee /etc/apt/sources.list.d/hhvm.list
RUN curl -sL https://deb.nodesource.com/setup_iojs_3.x | bash -

RUN apt-get clean && \
    apt-get update && \
    apt-get install -y sqlite hhvm build-essential git iojs bower && \
    rm -rf /var/lib/apt/lists/*

RUN git clone https://github.com/fruux/sabre-katana /etc/hhvm
WORKDIR /etc/hhvm
RUN make

CMD ["hhvm", "-a"]
