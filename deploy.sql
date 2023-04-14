CREATE TABLE IF NOT EXISTS Deployment (
	bundleName VARCHAR(255),
	currVersion INT,
	statusFlag TINYINT,
	path VARCHAR(255)
	)

ALTER TABLE Deployment ADD PRIMARY KEY(bundleName, currVersion);
