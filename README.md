# reviseit
A website to help students revise through quizzes and some simple games

This was made for my A-level computing coursework in 2015

The site is live at my personal website [here](http://reviseit.joesingo.co.uk)

**Update (Dec 2017):**
The site can now be deployed with Docker. To run, install Docker and docker-compose, and from the
root of the repo run

```
docker-compose build
docker-compose up -d
```

This will make the webserver available at `http://localhost:9301`.

The data directory in the MySQL container is mounted as a shared volume from a directory `db` on the
host in the root of the repo (directory will be created if it does not exist).

When running for the first time, create the required tables in the DB:

```
# Note container ID
docker ps | grep mysql

docker exec -it <container ID> bash -c "mysql -uroot -previseitroot < /db_setup.sql"
```
