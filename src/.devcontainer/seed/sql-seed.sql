DROP DATABASE IF EXISTS kanban_board;
CREATE DATABASE kanban_board;
USE kanban_board;

CREATE TABLE ColumnTable
(
    ColumnID INT
    AUTO_INCREMENT PRIMARY KEY,
    ColumnName VARCHAR
    (100) NOT NULL
);

    CREATE TABLE Users
    (
        UserID INT
        AUTO_INCREMENT PRIMARY KEY,
    UserName VARCHAR
        (200) NOT NULL
);

        CREATE TABLE Priority
        (
            PriorityID INT
            AUTO_INCREMENT PRIMARY KEY,
    PriorityName VARCHAR
            (100) NOT NULL
);

            CREATE TABLE Task
            (
                TaskID INT
                AUTO_INCREMENT PRIMARY KEY,
    ColumnID INT,
    TaskName VARCHAR
                (250) NOT NULL,
    Description VARCHAR
                (500) NOT NULL,
    UserID INT,
    PriorityID INT,
    CONSTRAINT FK_Column FOREIGN KEY
                (ColumnID) REFERENCES ColumnTable
                (ColumnID),
    CONSTRAINT FK_Users FOREIGN KEY
                (UserID) REFERENCES Users
                (UserID),
    CONSTRAINT FK_Priority FOREIGN KEY
                (PriorityID) REFERENCES Priority
                (PriorityID)
);

                -- Insert into ColumnTable
                INSERT INTO ColumnTable
                    (ColumnName)
                VALUES
                    ('TO DO'),
                    ('IN PROGRESS'),
                    ('DONE'),
                    ('APPROVED');

                -- Insert into Users
                INSERT INTO Users
                    (UserName)
                VALUES
                    ('Artemis'),
                    ('Linus'),
                    ('Mariam');

                -- Insert into Priority
                INSERT INTO Priority
                    (PriorityName)
                VALUES
                    ('Low'),
                    ('Medium'),
                    ('High');
    