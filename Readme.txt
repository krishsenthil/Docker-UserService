1. Extract the project files to any directory  under the user's root(I prefer to extract to /username/Sites in Mac os x) 
2. Please follow the link to install docker on your machine
3. Open the docker quickstart terminal and navigate to the folder where the project code is extracted
4. navigate to config folder
5. In the terminal execute the steup file following command => $ ./setup (this will create the project diretory in the docker container)
6. type "n" (No) for creating new project. 
7. In the terminal execute the run file following command =>  $ ./run  (initiates apache applicaiton will be ready to execute)
8. copy the ip address (http://192.168.99.100:1011) and enter it in the browser


Other Notes: 
---------------
Mysql username => root 
Mysql Password => password

default module (end user module to subscribe or unsubscribe) => http://192.168.99.100:1011/
admin module => http://192.168.99.100:1011/admin

Admin activites
-----------------
create newsletter - http://192.168.99.100:1011/admin/newsletter/create
list  all newsletters - http://192.168.99.100:1011/admin/newsletter/list

edit & delete can be performed from the list page



application admin login credentials 
-----------------------------------
user name - admin
password - password



Please execute the following command to login to the docker container to execute the commands. 

$ docker exec -it news(name of the container) bash 


Once logged in, impor the mysql dump located @ /var/www/news/sql/newsletter_latest.sql to create the tables and some test data




