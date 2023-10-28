CREATE DATABASE IF NOT EXISTS IT490;

USE IT490;

CREATE TABLE IF NOT EXISTS users (
    UserId INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(255) NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Email VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS sessions (
    SessionID VARCHAR(255) PRIMARY KEY,
    data TEXT,
    CreationTime TIMESTAMP NOT NULL,
    ExpiryTime TIMESTAMP NOT NULL
);

CREATE TABLE IF NOT EXISTS ratings (
    mealID int PRIMARY KEY,
    accountID varCHar(255),
    rating int
);

CREATE TABLE IF NOT EXISTS custom_recipes (
    MealID int PRIMARY KEY,
    strMeal varChar(255),
    strMealThumb varChar(255),
    strCatagory varChar(255),
    strInstructions varChar(255),
    strTags varChar(255),
    mealLink varChar(255),
    strArea varChar(255)
);
COMMIT;

/*
Creating Test User Account

INSERT INTO `users`(UserId,Username,`Password`, Email) VALUES (1, 'testUser', '12345', 'testUser@test.com');
*/

