-- Parser Pattern Edge Table
-- Stores edges between pattern nodes with pattern-specific metadata
--
-- This table is part of the parser pattern graph optimization. Unlike nodes (which are
-- deduplicated and shared), edges are pattern-specific. Multiple edges can exist between
-- the same two nodes if they appear in different construction patterns.
--
-- Example: If patterns P1 and P2 both have an edge from node A to node B, there will be
-- two separate edge records with pattern_id=P1 and pattern_id=P2.

CREATE TABLE parser_pattern_edge (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Pattern identification (which construction this edge belongs to)
    -- References parser_construction_v4.idConstruction
    pattern_id INT UNSIGNED NOT NULL,

    -- Edge endpoints (references to shared nodes in parser_pattern_node)
    from_node_id INT UNSIGNED NOT NULL,
    to_node_id INT UNSIGNED NOT NULL,

    -- Edge properties from original pattern graph (bypass, label, etc.)
    -- Stores pattern-specific edge metadata:
    --   {"bypass": true} - indicates optional element (can skip this edge)
    --   {"label": "optional modifier"} - edge label for visualization
    -- NULL if edge has no special properties
    properties TEXT NULL COMMENT 'Edge metadata: {"bypass": true, "label": "optional"}',

    -- Sequence order within pattern (for reconstruction and determinism)
    -- Used when multiple edges leave the same node (e.g., alternatives)
    -- Lower sequence = evaluated first
    sequence INT UNSIGNED DEFAULT 0,

    -- Timestamps
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    -- Indexes for graph traversal performance
    -- Most common query: "Given pattern_id and from_node_id, find all outgoing edges"
    INDEX idx_pattern_id (pattern_id),
    INDEX idx_from_node (from_node_id),
    INDEX idx_to_node (to_node_id),
    INDEX idx_pattern_from (pattern_id, from_node_id),  -- Composite index for common traversal

    -- Foreign keys with CASCADE delete
    -- If a node is deleted, all edges referencing it are deleted
    -- If a construction is deleted, all its edges are deleted
    FOREIGN KEY (from_node_id)
        REFERENCES parser_pattern_node(id)
        ON DELETE CASCADE,

    FOREIGN KEY (to_node_id)
        REFERENCES parser_pattern_node(id)
        ON DELETE CASCADE,

    FOREIGN KEY (pattern_id)
        REFERENCES parser_construction_v4(idConstruction)
        ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Pattern graph edges - multiple edges between same nodes allowed (one per pattern)';
