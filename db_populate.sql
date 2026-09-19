INSERT INTO Department (department_name)
VALUES
('Emergency'),
('Intensive Care Unit'),
('Surgery'),
('Oncology'),
('Infectious Disease Unit'),
('Pulmonology'),
('Pediatrics');


INSERT INTO Protocol (protocol_transmission, protocol_recommendation)
VALUES
('contact', 'a'),
('aerosol', 'b'),
('droplet', 'c'),
('vectorborne', 'd'),
('bloodborne', 'e'),
('fecal-oral', 'f');


INSERT INTO Pathogen (pathogen_name, pathogen_type, pathogen_transmission, pathogen_risk_category, protocol_id)
VALUES
('Enterococcus faecium', 'bacterium', 'contact', 2, 1),
('Staphylococcus aureus', 'bacterium', 'contact', 2, 1),
('Klebsiella pneumoniae', 'bacterium', 'contact', 2, 1),
('Acinetobacter baumannii', 'bacterium', 'contact', 2, 1),
('Pseudomonas aeruginosa', 'bacterium', 'contact', 2, 1),
('Escherichia coli', 'bacterium', 'fecal-oral', 2, 6),
('Influenza A', 'virus', 'droplet', 2, 3),
('Norovirus', 'virus', 'fecal-oral', 2, 6),
('Candida auris', 'fungus', 'contact', 2, 1),
('Plasmodium falciparum', 'parasite', 'vectorborne', 2, 4),
('Mycobacterium tuberculosis', 'bacterium', 'droplet', 3 , 3),
('Ebola', 'virus', 'contact', 4, 1);


INSERT INTO severity_level (severity_name, severity_risk_score)
VALUES
('mild', 1),
('moderate', 2),
('severe', 3);


INSERT INTO System_user (user_id, user_username, user_password_hash, user_name, user_surname, user_email, user_phone_number, user_role)
VALUES
('550e8400-e29b-41d4-a716-446655440000', 'testdoctor', 'test', 'name1', 'surname1', 'doctor@test.com', '1', 'doctor'),
('550e8400-e29b-41d4-a716-446655440001', 'testepidemiologist', 'test', 'name2', 'surname3', 'epidemiologist@test.com', '2', 'epidemiologist');