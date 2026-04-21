-- ============================================================================
-- Parser V5 Database Schema Migration Script
-- ============================================================================
--
-- This script creates the necessary tables and modifications for Parser V5
-- Ghost Nodes and Type Graph Architecture.
--
-- Features:
-- - Type Graph for unified construction ontology
-- - State Snapshots for debugging and visualization
-- - Extension of parser_construction_v4 for mandatory element tracking
--
-- Execution: Run this script on your database
-- Rollback: See rollback script at the end
--
-- ============================================================================

-- ============================================================================
-- 1. CREATE TABLE: parser_type_graph_v5
-- ============================================================================
-- Stores unified Type Graph representing all construction relationships,
-- mandatory elements, and CE hierarchies.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `parser_type_graph_v5` (
    `idTypeGraph` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `idGrammarGraph` INT UNSIGNED NOT NULL,

    -- Complete Type Graph structure
    `graphData` TEXT NOT NULL COMMENT 'Complete Type Graph structure with nodes and edges',
    `nodes` TEXT NOT NULL COMMENT 'All constructions + CE labels as nodes',
    `edges` TEXT NOT NULL COMMENT 'Relationships: produces, requires, inherits',

    -- Derived indexes for fast lookup
    `mandatoryElements` TEXT NOT NULL COMMENT 'Map: constructionId -> mandatory CE labels',
    `ceHierarchy` TEXT NOT NULL COMMENT 'CE dependency chains (e.g., Head -> Mod -> Adp)',

    -- Metadata
    `version` VARCHAR(10) NOT NULL DEFAULT 'v5',
    `createdAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`idTypeGraph`),
    INDEX `idx_grammar` (`idGrammarGraph`),
    INDEX `idx_version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Parser V5: Unified Type Graph for construction ontology';


-- ============================================================================
-- 2. CREATE TABLE: parser_construction_relationship_v5
-- ============================================================================
-- Stores construction-to-construction and construction-to-CE relationships
-- extracted from the Type Graph.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `parser_construction_relationship_v5` (
    `idRelationship` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `idGrammarGraph` INT UNSIGNED NOT NULL,

    -- Source (can be construction or CE label)
    `sourceType` VARCHAR(255) NOT NULL,
    `sourceId` INT UNSIGNED NULL COMMENT 'Construction ID or NULL for CE',
    `sourceName` VARCHAR(100) NOT NULL COMMENT 'Construction name or CE label',

    -- Relationship type
    `relationshipType` VARCHAR(255) NOT NULL,

    -- Target (can be construction or CE label)
    `targetType` VARCHAR(255) NOT NULL,
    `targetId` INT UNSIGNED NULL COMMENT 'Construction ID or NULL for CE',
    `targetName` VARCHAR(100) NOT NULL,

    -- Relationship properties
    `mandatory` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Is this relationship mandatory?',
    `metadata` TEXT NULL COMMENT 'Additional relationship properties',

    PRIMARY KEY (`idRelationship`),
    INDEX `idx_grammar` (`idGrammarGraph`),
    INDEX `idx_source` (`sourceType`, `sourceId`),
    INDEX `idx_target` (`targetType`, `targetId`),
    INDEX `idx_relationship_type` (`relationshipType`),
    INDEX `idx_mandatory` (`mandatory`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Parser V5: Construction and CE relationships';


-- ============================================================================
-- 3. CREATE TABLE: parser_state_snapshot_v5
-- ============================================================================
-- Stores position-by-position snapshots of parse state for debugging,
-- visualization, and step-by-step evaluation.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `parser_state_snapshot_v5` (
    `idSnapshot` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `idParserGraph` INT NOT NULL,
    `position` INT NOT NULL,

    -- Token data at this position
    `tokenData` TEXT NULL,

    -- Token Graph state (nodes + edges)
    `tokenGraph` TEXT NOT NULL,

    -- Active alternatives at this position
    `activeAlternatives` TEXT NULL,

    -- Ghost nodes state
    `ghostNodes` TEXT NULL,

    -- Confirmed nodes and edges
    `confirmedNodes` TEXT NULL,
    `confirmedEdges` TEXT NULL,

    -- Recent reconfiguration operations (last 10)
    `reconfigurations` TEXT NULL,

    -- Statistics at this position
    `statistics` TEXT NULL,

    -- Metadata
    `capturedAt` TIMESTAMP NOT NULL,
    `processingTime` FLOAT NULL COMMENT 'Time in seconds to reach this position',

    PRIMARY KEY (`idSnapshot`),
    INDEX `idx_parser_graph` (`idParserGraph`),
    INDEX `idx_position` (`position`),
    INDEX `idx_parser_position` (`idParserGraph`, `position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Parser V5: State snapshots for debugging and visualization';


-- ============================================================================
-- 4. ALTER TABLE: parser_construction_v4 (V5 Extensions)
-- ============================================================================
-- Extends existing parser_construction_v4 table with V5 fields for
-- mandatory element tracking and ghost creation rules.
-- ============================================================================

-- Check if columns already exist before adding them
SET @dbname = DATABASE();
SET @tablename = 'parser_construction_v4';

-- Add mandatoryElements column if it doesn't exist
SET @columnExists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
    AND TABLE_NAME = @tablename
    AND COLUMN_NAME = 'mandatoryElements'
);

SET @sql = IF(@columnExists = 0,
    'ALTER TABLE `parser_construction_v4`
     ADD COLUMN `mandatoryElements` TEXT NULL
     COMMENT ''Elements required by this construction (can create ghosts)''
     AFTER `compiledPattern`',
    'SELECT ''Column mandatoryElements already exists'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add optionalElements column if it doesn't exist
SET @columnExists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
    AND TABLE_NAME = @tablename
    AND COLUMN_NAME = 'optionalElements'
);

SET @sql = IF(@columnExists = 0,
    'ALTER TABLE `parser_construction_v4`
     ADD COLUMN `optionalElements` TEXT NULL
     COMMENT ''Elements that are optional in this construction''
     AFTER `mandatoryElements`',
    'SELECT ''Column optionalElements already exists'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add ghostCreationRules column if it doesn't exist
SET @columnExists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
    AND TABLE_NAME = @tablename
    AND COLUMN_NAME = 'ghostCreationRules'
);

SET @sql = IF(@columnExists = 0,
    'ALTER TABLE `parser_construction_v4`
     ADD COLUMN `ghostCreationRules` TEXT NULL
     COMMENT ''Rules for when/how to create ghost nodes''
     AFTER `optionalElements`',
    'SELECT ''Column ghostCreationRules already exists'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- ============================================================================
-- VERIFICATION QUERIES
-- ============================================================================
-- Run these queries to verify the migration was successful
-- ============================================================================

-- Verify all V5 tables exist
SELECT
    'V5 Tables' AS verification_type,
    TABLE_NAME,
    TABLE_ROWS,
    CREATE_TIME
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME IN (
    'parser_type_graph_v5',
    'parser_construction_relationship_v5',
    'parser_state_snapshot_v5'
)
ORDER BY TABLE_NAME;

-- Verify parser_construction_v4 extensions
SELECT
    'V4 Extensions' AS verification_type,
    COLUMN_NAME,
    DATA_TYPE,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'parser_construction_v4'
AND COLUMN_NAME IN (
    'mandatoryElements',
    'optionalElements',
    'ghostCreationRules'
)
ORDER BY ORDINAL_POSITION;


-- ============================================================================
-- ROLLBACK SCRIPT
-- ============================================================================
-- Use this script to completely remove V5 schema changes and revert to V4
-- WARNING: This will delete all V5 data!
-- ============================================================================

/*

-- Drop V5 tables
DROP TABLE IF EXISTS `parser_state_snapshot_v5`;
DROP TABLE IF EXISTS `parser_construction_relationship_v5`;
DROP TABLE IF EXISTS `parser_type_graph_v5`;

-- Remove V5 columns from parser_construction_v4
ALTER TABLE `parser_construction_v4`
    DROP COLUMN IF EXISTS `ghostCreationRules`,
    DROP COLUMN IF EXISTS `optionalElements`,
    DROP COLUMN IF EXISTS `mandatoryElements`;

*/


-- ============================================================================
-- SAMPLE DATA (for testing)
-- ============================================================================
-- Uncomment to insert sample data for development/testing
-- ============================================================================

/*

-- Sample Type Graph (minimal example)
INSERT INTO `parser_type_graph_v5` (
    `idGrammarGraph`,
    `graphData`,
    `nodes`,
    `edges`,
    `mandatoryElements`,
    `ceHierarchy`,
    `version`
) VALUES (
    1,
    '{"nodeCount": 10, "edgeCount": 15}',
    '[{"id": 1, "type": "construction", "name": "cxn_simple_clause"}]',
    '[{"source": 1, "target": 2, "type": "produces"}]',
    '{"1": ["ARG_SUBJ", "PRED"]}',
    '{"HEAD": ["MOD_DET", "MOD_ADJ"]}',
    'v5'
);

-- Sample Construction Relationship
INSERT INTO `parser_construction_relationship_v5` (
    `idGrammarGraph`,
    `sourceType`,
    `sourceId`,
    `sourceName`,
    `relationshipType`,
    `targetType`,
    `targetId`,
    `targetName`,
    `mandatory`
) VALUES (
    1,
    'construction',
    1,
    'cxn_simple_clause',
    'produces',
    'ce_label',
    NULL,
    'ARG_SUBJ',
    TRUE
);

*/


-- ============================================================================
-- END OF MIGRATION SCRIPT
-- ============================================================================

-- Display success message
SELECT
    'Parser V5 schema migration completed successfully!' AS status,
    NOW() AS executed_at;
