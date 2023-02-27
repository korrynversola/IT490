CREATE TABLE IF NOT EXISTS Rated_Recipes (
	email VARCHAR(255) NOT NULL,
	recipeID INT NOT NULL,
	rating TINYINT,
	FOREIGN KEY (email) REFERENCES Users(email),
	FOREIGN KEY (recipeID) REFERENCES Recipes(recipeID)
	);
