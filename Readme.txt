1. Extract the project files to any directory under the user's root
2. Please follow the link to install docker on your machine => https://docs.docker.com/engine/installation/
3. Open the docker quickstart terminal and navigate to the folder where the project code is extracted
4. Navigate to config folder
5. In the terminal execute the steup file using following command => $ ./setup (this will create the project diretory in the docker container)
6. type "n" (No) for creating new project. 
7. In the terminal execute the run file using following command =>  $ ./run  (initiates apache application; it will be ready to execute)
8. copy the ip address (http://192.168.99.100:1001) and enter it in the browser


Other Notes: 
------------
Mysql username => root 
Mysql Password => password

default module => http://192.168.99.100:1001


To create MySql user permissions and tables
--------------------------------------------
Please execute the following command to login to the docker container to execute the commands. 

$ docker exec -it service(name of the container) bash 


Once logged in to the machine, 
1. Execute the mysql permissions file located @ /var/www/service/sql/9_grant_permissions.sql to create the mysql user for our application
2. Import the mysql dump located @ /var/www/service/sql/1_ddl.sql to create the tables
