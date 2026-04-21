-- Parser Pattern Node Table
-- Stores unique, deduplicated pattern nodes shared across all constructions
--
-- This table is part of the parser pattern graph optimization that replaces
-- the nested-loop pattern matching approach with a shared graph structure.
--
-- Node deduplication: Nodes with identical specifications (type + properties)
-- are shared across multiple construction patterns using a spec_hash for uniqueness.

CREATE TABLE parser_pattern_node (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Node type: START, END, LITERAL, SLOT, CE_SLOT, COMBINED_SLOT,
    -- CONSTRUCTION_REF, WILDCARD, INTERMEDIATE, REP_CHECK
    type VARCHAR(50) NOT NULL,

    -- Node specification as TEXT (type-specific fields)
    -- Examples:
    --   LITERAL: {"type": "LITERAL", "value": "café"}
    --   SLOT: {"type": "SLOT", "pos": "NOUN", "constraint": null}
    --   CE_SLOT: {"type": "CE_SLOT", "ce_label": "Head", "ce_tier": "phrasal"}
    --   COMBINED_SLOT: {"type": "COMBINED_SLOT", "pos": "NOUN", "ce_label": "Head", "ce_tier": "phrasal", "constraint": null}
    --   CONSTRUCTION_REF: {"type": "CONSTRUCTION_REF", "construction_name": "MOD", "construction_id": null}
    --   WILDCARD: {"type": "WILDCARD"}
    specification TEXT NOT NULL,

    -- Unique hash for deduplication (SHA256 of canonical TEXT)
    -- Generated from normalized/canonical specification to ensure identical nodes
    -- produce the same hash regardless of field order or case differences
    spec_hash VARCHAR(64) NOT NULL UNIQUE,

    -- Denormalized fields for efficient querying (extracted from specification)
    -- These allow fast filtering without parsing TEXT
    value VARCHAR(255) NULL COMMENT 'For LITERAL nodes - the literal word',
    pos VARCHAR(50) NULL COMMENT 'For SLOT/COMBINED_SLOT nodes - the POS tag',
    ce_label VARCHAR(100) NULL COMMENT 'For CE_SLOT/COMBINED_SLOT nodes - the CE label',
    ce_tier VARCHAR(50) NULL COMMENT 'For CE_SLOT/COMBINED_SLOT nodes - phrasal/clausal/sentential',
    construction_name VARCHAR(255) NULL COMMENT 'For CONSTRUCTION_REF nodes - referenced construction name',

    -- Statistics
    usage_count INT UNSIGNED DEFAULT 0 COMMENT 'Number of edges referencing this node (updated during graph build)',

    -- Timestamps
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    -- Indexes for efficient querying
    INDEX idx_type (type),
    INDEX idx_spec_hash (spec_hash),
    INDEX idx_value (value),
    INDEX idx_pos (pos),
    INDEX idx_ce_label (ce_label),
    INDEX idx_construction_name (construction_name)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Shared pattern graph nodes - deduplicated across all construction patterns';
