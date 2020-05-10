# Expenses Tracker
> Manage your finances and stop wondering where you spent your money.

## Prerequisites
- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/install/)

## Run 
- Clone the project
  ```
  git clone git@github.com:snaksa/expense-tracker-backend.git
  cd expense-tracker-backend
  ```

- Build the containers
  ```
  make build
  make up
  ```

- Install dependencies
  ```
  make dependencies
  ```

- Run migrations and fixtures
  ```
  make migrate
  make fixtures
  ```

- Go to [http://localhost:8080/graphiql](http://localhost:8080/graphiql)
- Run the following login mutation
  ```
  mutation login {
    loginUser(input: {email: "demo@gmail.com", password: "123456"})
  }
  ```
- You should get the following response
  ```
  {
    "data": {
      "loginUser": "<yourApiToken>"
    }
  }
  ```
