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

CREATE TABLE IF NOT EXISTS custom_meals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    strMeal VARCHAR(255) NOT NULL,
    strCategory VARCHAR(255),
    strArea VARCHAR(255),
    strInstructions TEXT,
    strMealThumb VARCHAR(255),
    strTags VARCHAR(255),
    strIngredient1 VARCHAR(255),
    strIngredient2 VARCHAR(255),
    strIngredient3 VARCHAR(255),
    strIngredient4 VARCHAR(255),
    strIngredient5 VARCHAR(255),
    strIngredient6 VARCHAR(255),
    strIngredient7 VARCHAR(255),
    strIngredient8 VARCHAR(255),
    strIngredient9 VARCHAR(255),
    strIngredient10 VARCHAR(255),
    strIngredient11 VARCHAR(255),
    strIngredient12 VARCHAR(255),
    strIngredient13 VARCHAR(255),
    strIngredient14 VARCHAR(255),
    strIngredient15 VARCHAR(255),
    strIngredient16 VARCHAR(255),
    strIngredient17 VARCHAR(255),
    strIngredient18 VARCHAR(255),
    strIngredient19 VARCHAR(255),
    strIngredient20 VARCHAR(255),
    strMeasure1 VARCHAR(255),
    strMeasure2 VARCHAR(255),
    strMeasure3 VARCHAR(255),
    strMeasure4 VARCHAR(255),
    strMeasure5 VARCHAR(255),
    strMeasure6 VARCHAR(255),
    strMeasure7 VARCHAR(255),
    strMeasure8 VARCHAR(255),
    strMeasure9 VARCHAR(255),
    strMeasure10 VARCHAR(255),
    strMeasure11 VARCHAR(255),
    strMeasure12 VARCHAR(255),
    strMeasure13 VARCHAR(255),
    strMeasure14 VARCHAR(255),
    strMeasure15 VARCHAR(255),
    strMeasure16 VARCHAR(255),
    strMeasure17 VARCHAR(255),
    strMeasure18 VARCHAR(255),
    strMeasure19 VARCHAR(255),
    strMeasure20 VARCHAR(255),
    strYoutube VARCHAR(255),
    datePublished DATE ,
    created_by VARCHAR(255) NOT NULL
);
COMMIT;

/*
Creating Test User Account

INSERT INTO `users`(UserId,Username,`Password`, Email) VALUES (1, 'testUser', '12345', 'testUser@test.com');
*/
