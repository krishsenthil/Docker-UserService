# Apache, PHP, MySQL, Zend, Mongodb and SSHd installation
#
# Currently gets to working Zend installation without config

# Use Ubuntu 12.04 as base image
FROM ubuntu:14.04

#MAINTAINER Senthilraj K, senthilrajk@gmail.com

# Create a random password for root and MySQL and save to "/root/pw.txt"
RUN  echo "password" > /root/pw.txt

# Change the root password
RUN echo "root:$(cat /root/pw.txt)" | chpasswd

# Add Ubuntu mirrors
#RUN echo 'deb https://apt.dockerproject.org/repo ubuntu-trusty main' > /etc/apt/sources.list

# Update package lists
RUN apt-get update

RUN apt-get autoclean

# Add MySQL root password to debconf
RUN bash -c "debconf-set-selections <<< 'mysql-server mysql-server/root_password password $(cat /root/pw.txt)'"
RUN bash -c "debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password $(cat /root/pw.txt)'"

RUN apt-get -y install apache2

# Install packages
RUN apt-get -y install git apache2 php5 libapache2-mod-php5  php5-curl php5-mcrypt php5-gd php5-mysql openssh-server
RUN apt-get -y install mysql-server libapache2-mod-auth-mysql php5-mysql


RUN php5enmod mcrypt

# Create the SSHd working directory
RUN mkdir /var/run/sshd

# Enable Apache rewrite module
RUN a2enmod rewrite

# Add the Apache virtual host file
ADD apache_vhost /etc/apache2/sites-available/service.conf

# Change owner of files in web root to "www-data:www-data"
RUN chown www-data:www-data -R /var/www

RUN a2dissite 000-default

RUN a2ensite service

#RUN service apache2 reload

VOLUME /var/www

# Create "/root/run.sh" startup script
RUN bash -c "echo -e \"\x23\x21/bin/bash\nservice apache2 start\nmysqld --log --log-error \x26\n/usr/sbin/sshd -D \x26\nwait \x24\x7b\x21\x7d\n\" > /root/run.sh"

# Change "/root/run.sh" to be executable
RUN chmod +x /root/run.sh

# Create the "collash" database
RUN (mysqld &) ; sleep 5 && mysql -u root -p$(cat /root/pw.txt) -e "CREATE DATABASE db_userservice;" ; kill -TERM $(cat /var/run/mysqld/mysqld.pid)

# Display the password and delete "/root/pw.txt"
RUN bash -c "echo -e \"\n*********************************\nRecord the root / MySQL Password\x21\";echo $(cat /root/pw.txt);echo -e \"*********************************\n\"; rm -f /root/pw.txt"

# Set the entry point to "/root/run.sh"
ENTRYPOINT ["/root/run.sh"]

# Expose SSH and HTTP ports
EXPOSE 22 80 3306
