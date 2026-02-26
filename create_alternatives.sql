USE dent_consent;

CREATE TABLE IF NOT EXISTS procedure_alternatives (
    id INT AUTO_INCREMENT PRIMARY KEY,
    operation_type_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    pros TEXT,
    cons TEXT,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (operation_type_id) REFERENCES operation_types(id) ON DELETE CASCADE
);

TRUNCATE TABLE procedure_alternatives;

-- 1: Single Tooth Implant
INSERT INTO procedure_alternatives (operation_type_id, name, pros, cons) VALUES 
(1, 'Dental Crown', 'Faster, cheaper, no surgery', 'Requires shaving healthy tooth, shorter life'),
(1, 'Tooth-Supported Bridge', 'No implant surgery', 'Affects two healthy teeth'),
(1, 'Partial Denture', 'Removable, no surgery', 'Less stable, may feel bulky');

-- 2: Multiple Tooth Implant
INSERT INTO procedure_alternatives (operation_type_id, name, pros, cons) VALUES
(2, 'Traditional Bridge', 'Fixed restoration, faster than implants', 'Requires altering adjacent teeth'),
(2, 'Partial Denture', 'Cost-effective, non-invasive', 'Removable, functional limitations');

-- 3: Full Arch Implant
INSERT INTO procedure_alternatives (operation_type_id, name, pros, cons) VALUES
(3, 'Complete Denture', 'No surgery required, economical', 'Can slip while eating, bone loss over time'),
(3, 'Implant-Supported Denture', 'More stable than traditional dentures, removable for cleaning', 'Requires surgery, takes months to heal');

-- 4: Implant-Supported Bridge
INSERT INTO procedure_alternatives (operation_type_id, name, pros, cons) VALUES
(4, 'Traditional Bridge', 'No surgery needed, quicker result', 'Fails if supporting teeth decay'),
(4, 'Partial Denture', 'Cheaper, no surgery', 'Removable, metal clasps may be visible'),
(4, 'Multiple Single Crowns', 'Independent restorations, easier to floss', 'Requires more implants, higher cost');

-- 5: Implant-Supported Denture
INSERT INTO procedure_alternatives (operation_type_id, name, pros, cons) VALUES
(5, 'Complete Denture', 'Economical, no surgical phase', 'Poor retention, affects chewing'),
(5, 'Traditional Partial Denture', 'Cheaper, easier to make', 'Relies on remaining teeth, bone resorption');

-- 6: Bone Grafting (Support)
INSERT INTO procedure_alternatives (operation_type_id, name, pros, cons) VALUES
(6, 'No grafting (if possible)', 'Saves time and money, less surgery', 'Higher risk of implant failure if bone is insufficient'),
(6, 'Different grafting material', 'Avoids taking bone from another site', 'May take longer to integrate'),
(6, 'Shorter implant without graft', 'Less invasive, faster recovery', 'May not bear heavy bite forces');

-- 7: Crown
INSERT INTO procedure_alternatives (operation_type_id, name, pros, cons) VALUES
(7, 'Veneer', 'More conservative, preserves tooth structure', 'Only fixes front surface, prone to chipping'),
(7, 'Composite Filling', 'One visit, cheapest option', 'Stains easily, lacks strength of the crown'),
(7, 'Extraction + Implant', 'Permanent fix, highly durable', 'Invasive, expensive, long process');

-- 8: Bridge
INSERT INTO procedure_alternatives (operation_type_id, name, pros, cons) VALUES
(8, 'Implant-Supported Bridge', 'Does not damage adjacent teeth, prevents bone loss', 'Requires surgery, more expensive'),
(8, 'Partial Denture', 'No grinding of teeth, affordable', 'Removable, less comfortable'),
(8, 'Multiple Crowns', 'Individual teeth, easy to clean', 'Needs an implant for each missing tooth');

-- 9: Complete Denture
INSERT INTO procedure_alternatives (operation_type_id, name, pros, cons) VALUES
(9, 'Implant-Supported Denture', 'Excellent stability, improves chewing force', 'High cost, requires surgery'),
(9, 'Full Arch Implant', 'Fixed permanently, feels like natural teeth', 'Most expensive, complex procedure');

-- 10: Partial Denture
INSERT INTO procedure_alternatives (operation_type_id, name, pros, cons) VALUES
(10, 'Implant-Supported Partial', 'Very stable, no metal clasps', 'Requires surgery, healing time'),
(10, 'Fixed Bridge', 'Non-removable, feels natural', 'Requires filing down healthy abutment teeth');

-- 11: Veneer
INSERT INTO procedure_alternatives (operation_type_id, name, pros, cons) VALUES
(11, 'Crown', 'Covers entire tooth, stronger', 'Requires removal of more tooth structure'),
(11, 'Composite Bonding', 'Single visit, cheaper, easy to repair', 'Stains over time, less durable'),
(11, 'No treatment (if minor)', 'Zero cost, no enamel removal', 'Does not fix cosmetic imperfections');

-- 12: Full Mouth Rehab.
INSERT INTO procedure_alternatives (operation_type_id, name, pros, cons) VALUES
(12, 'Full Arch Implants', 'Permanent, mimics natural teeth', 'Most invasive, expensive'),
(12, 'Complete Dentures', 'Least invasive, budget-friendly', 'Bone loss, removable'),
(12, 'Combination (Crowns + Bridges)', 'Saves remaining healthy teeth', 'Complex treatment plan, varying durability');
