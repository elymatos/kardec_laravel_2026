-- Add Grammar Graph Support to Parser Pattern Tables
-- This script adds fields to support cross-construction linking (grammar graph)
--
-- Purpose: Enable building a unified grammar graph that links construction patterns
-- by connecting each construction's END node to all CONSTRUCTION_REF nodes that reference it.
--
-- Usage: mysql -u username -p database_name < database/sql/add_grammar_graph_fields.sql

-- Add edge_type to distinguish intra-pattern vs inter-pattern edges
ALTER TABLE parser_pattern_edge
ADD COLUMN edge_type ENUM('INTRA_PATTERN', 'INTER_PATTERN')
DEFAULT 'INTRA_PATTERN'
COMMENT 'INTRA_PATTERN = within construction, INTER_PATTERN = cross-construction link'
AFTER pattern_id;

ALTER TABLE parser_pattern_edge
ADD INDEX idx_edge_type (edge_type);

-- Add owner_construction_id to track which construction each END node belongs to
ALTER TABLE parser_pattern_node
ADD COLUMN owner_construction_id INT(10) UNSIGNED NULL
COMMENT 'For END nodes - which construction this END represents'
AFTER construction_name;

ALTER TABLE parser_pattern_node
ADD INDEX idx_owner_construction (owner_construction_id);

-- Add foreign key for owner_construction_id
ALTER TABLE parser_pattern_node
ADD CONSTRAINT fk_owner_construction
FOREIGN KEY (owner_construction_id)
REFERENCES parser_construction_v4(idConstruction)
ON DELETE SET NULL;

-- Backfill existing data
-- Mark all existing edges as INTRA_PATTERN
UPDATE parser_pattern_edge
SET edge_type = 'INTRA_PATTERN'
WHERE edge_type IS NULL;

-- Set owner_construction_id for existing END nodes
-- Find END nodes by looking for nodes that are targets of edges and have no outgoing edges
UPDATE parser_pattern_node n
JOIN parser_pattern_edge e ON n.id = e.to_node_id
SET n.owner_construction_id = e.pattern_id
WHERE n.type = 'END'
  AND e.pattern_id > 0
  AND n.owner_construction_id IS NULL
  AND NOT EXISTS (
    SELECT 1 FROM parser_pattern_edge e2 WHERE e2.from_node_id = n.id
  );
