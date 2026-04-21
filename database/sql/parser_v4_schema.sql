-- ============================================================================
-- Parser V4 Schema: Unified Constructional Parser with Multi-Level CE Labels
-- ============================================================================
-- This schema supports the V4 incremental constructional parser with:
-- - Unified construction registry (MWE, phrasal, clausal, sentential)
-- - Multi-level CE label assignment (phrasal, clausal, sentential)
-- - MWE lookahead for ambiguous boundary detection
-- - BNF-like patterns with constraint checking
-- - Parallel alternative evaluation with priority-based resolution
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Grammar Configuration
-- ----------------------------------------------------------------------------

-- Grammar graphs define language-specific parsing configurations
CREATE TABLE IF NOT EXISTS parser_grammar_graph (
    idGrammarGraph INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL COMMENT 'Grammar name (e.g., "Portuguese Basic Grammar")',
    language VARCHAR(10) NOT NULL COMMENT 'ISO language code (e.g., "pt", "en")',
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_language (language)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Grammar configurations for different languages';

-- ----------------------------------------------------------------------------
-- Unified Construction Registry (V4)
-- ----------------------------------------------------------------------------

-- All construction patterns (MWE, phrasal, clausal, sentential) in one table
CREATE TABLE IF NOT EXISTS parser_construction_v4 (
    idConstruction BIGINT AUTO_INCREMENT PRIMARY KEY,
    idGrammarGraph INT NOT NULL COMMENT 'Grammar this construction belongs to',

    -- Identification
    name VARCHAR(100) NOT NULL COMMENT 'Construction name (e.g., "café_da_manhã", "NP_Det_Noun")',
    constructionType VARCHAR(20) NOT NULL COMMENT 'mwe, phrasal, clausal, sentential',

    -- Pattern definition
    pattern TEXT NOT NULL COMMENT 'BNF-like pattern (e.g., "café" "da" "manhã", {NOUN} {VERB})',
    compiledPattern JSON NULL COMMENT 'Pre-compiled BNF graph for fast matching',

    -- Priority and activation
    priority SMALLINT DEFAULT 50 COMMENT 'Resolution priority: MWE=100-199, Phrasal=50-99, Clausal=20-49, Sentential=1-19',
    enabled BOOLEAN DEFAULT TRUE COMMENT 'Whether this construction is active',

    -- Multi-level CE Labels (one per linguistic level)
    phrasalCE VARCHAR(20) NULL COMMENT 'Phrasal level: Head, Mod, Adp, Lnk, Clf, Idx, Conj',
    clausalCE VARCHAR(20) NULL COMMENT 'Clausal level: Pred, Arg, CPP, Gen, FPM, Conj',
    sententialCE VARCHAR(20) NULL COMMENT 'Sentential level: Main, Adv, Rel, Comp, Dtch, Int',

    -- Constraints (feature agreement, POS, dependencies)
    constraints JSON DEFAULT '[]' COMMENT 'Array of constraint rules',

    -- MWE-specific fields
    aggregateAs VARCHAR(255) NULL COMMENT 'How to aggregate MWE (e.g., "café_da_manhã")',
    semanticType VARCHAR(20) NULL COMMENT 'Semantic category of aggregated node',
    semantics JSON NULL COMMENT 'Additional semantic information',

    -- MWE Lookahead (for ambiguous boundaries like "gol contra")
    lookaheadEnabled BOOLEAN DEFAULT FALSE COMMENT 'Enable lookahead validation',
    lookaheadMaxDistance SMALLINT DEFAULT 2 COMMENT 'Max tokens to look ahead',
    invalidationPatterns JSON DEFAULT '[]' COMMENT 'Patterns that invalidate MWE if found',
    confirmationPatterns JSON DEFAULT '[]' COMMENT 'Patterns that confirm MWE boundary',

    -- Metadata
    description TEXT NULL COMMENT 'Human-readable description',
    examples JSON NULL COMMENT 'Example sentences using this construction',

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    UNIQUE KEY uq_construction_name (idGrammarGraph, name),
    INDEX idx_construction_type (constructionType),
    INDEX idx_construction_priority (priority),
    INDEX idx_construction_enabled (enabled),
    INDEX idx_construction_phrasal (phrasalCE),
    INDEX idx_construction_clausal (clausalCE),
    INDEX idx_construction_sentential (sententialCE),

    FOREIGN KEY (idGrammarGraph) REFERENCES parser_grammar_graph(idGrammarGraph) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Unified construction registry for all pattern types (V4)';

-- ----------------------------------------------------------------------------
-- Runtime Parse Instances
-- ----------------------------------------------------------------------------

-- Parse graphs represent individual parsing sessions (one per sentence)
CREATE TABLE IF NOT EXISTS parser_graph (
    idParserGraph INT AUTO_INCREMENT PRIMARY KEY,
    idGrammarGraph INT NOT NULL,
    sentence TEXT NOT NULL COMMENT 'Original sentence being parsed',
    status ENUM('parsing', 'complete', 'failed') DEFAULT 'parsing',
    errorMessage TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_status (status),
    INDEX idx_grammar (idGrammarGraph),
    FOREIGN KEY (idGrammarGraph) REFERENCES parser_grammar_graph(idGrammarGraph) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Individual parsing sessions (one per sentence)';

-- Parse nodes represent instantiated words and MWE aggregations
CREATE TABLE IF NOT EXISTS parser_node (
    idParserNode INT AUTO_INCREMENT PRIMARY KEY,
    idParserGraph INT NOT NULL,

    -- Node identification
    label VARCHAR(255) NOT NULL COMMENT 'Word or aggregated MWE phrase',
    type ENUM('E', 'R', 'A', 'F', 'MWE') NOT NULL COMMENT 'Entity, Relational, Attribute, Function, MWE',

    -- Activation state
    threshold INT DEFAULT 1 COMMENT 'Number of components needed for completion',
    activation INT DEFAULT 1 COMMENT 'Current activation level',
    isFocus BOOLEAN DEFAULT FALSE COMMENT 'Is this node in focus',

    -- Position tracking
    positionInSentence INT NOT NULL COMMENT 'Word position (0-indexed)',

    -- MWE tracking
    idMWE INT NULL COMMENT 'Legacy reference to parser_mwe (deprecated)',

    -- Morphological features (V4)
    features JSON NULL COMMENT 'Morphological features from UD parser',
    idLemma INT NULL COMMENT 'Reference to lemma table',

    -- Multi-level CE labels (V4)
    phrasalCE VARCHAR(20) NULL COMMENT 'Phrasal level CE: Head, Mod, Adp, Lnk, Clf, Idx, Conj',
    clausalCE VARCHAR(20) NULL COMMENT 'Clausal level CE: Pred, Arg, CPP, Gen, FPM, Conj',
    sententialCE VARCHAR(20) NULL COMMENT 'Sentential level CE: Main, Adv, Rel, Comp, Dtch, Int',

    -- Construction tracking (V4)
    constructionName VARCHAR(100) NULL COMMENT 'Which construction created this node',
    constructionId BIGINT NULL COMMENT 'FK to parser_construction_v4',

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    INDEX idx_parse_graph (idParserGraph),
    INDEX idx_position (idParserGraph, positionInSentence),
    INDEX idx_focus (idParserGraph, isFocus),
    INDEX idx_node_phrasal_ce (idParserGraph, phrasalCE),
    INDEX idx_node_clausal_ce (idParserGraph, clausalCE),
    INDEX idx_node_sentential_ce (idParserGraph, sententialCE),
    INDEX idx_node_construction (constructionId),

    FOREIGN KEY (idParserGraph) REFERENCES parser_graph(idParserGraph) ON DELETE CASCADE,
    FOREIGN KEY (constructionId) REFERENCES parser_construction_v4(idConstruction) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Instantiated nodes (words and MWE aggregations) with multi-level CE labels';

-- Parse links represent relationships between nodes
CREATE TABLE IF NOT EXISTS parser_link (
    idParserLink INT AUTO_INCREMENT PRIMARY KEY,
    idParserGraph INT NOT NULL,
    idSourceNode INT NOT NULL,
    idTargetNode INT NOT NULL,
    linkType ENUM('sequential', 'activate', 'dependency', 'prediction') DEFAULT 'dependency',
    weight DECIMAL(3,2) DEFAULT 1.0,
    stage VARCHAR(50) NULL COMMENT 'When this link was created (e.g., mwe, phrasal, clausal)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_parse_graph (idParserGraph),
    INDEX idx_source (idParserGraph, idSourceNode),
    INDEX idx_target (idParserGraph, idTargetNode),

    FOREIGN KEY (idParserGraph) REFERENCES parser_graph(idParserGraph) ON DELETE CASCADE,
    FOREIGN KEY (idSourceNode) REFERENCES parser_node(idParserNode) ON DELETE CASCADE,
    FOREIGN KEY (idTargetNode) REFERENCES parser_node(idParserNode) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Dependency and structural links between nodes';

-- ----------------------------------------------------------------------------
-- Debugging and Analysis (V4)
-- ----------------------------------------------------------------------------

-- Alternative log tracks construction alternatives during incremental parsing
CREATE TABLE IF NOT EXISTS parser_alternative_log (
    idAlternativeLog BIGINT AUTO_INCREMENT PRIMARY KEY,
    idParserGraph INT NOT NULL,

    -- Construction identification
    constructionName VARCHAR(100) NOT NULL,
    constructionType VARCHAR(20) NOT NULL COMMENT 'mwe, phrasal, clausal, sentential',

    -- Position tracking
    startPosition SMALLINT NOT NULL COMMENT 'Start word position',
    endPosition SMALLINT NULL COMMENT 'End word position (when completed)',

    -- Status tracking
    status VARCHAR(20) NOT NULL COMMENT 'pending, progressing, complete, tentative_complete, confirmed, invalidated, abandoned, aggregated',

    -- Activation levels
    activation DECIMAL(5,2) NULL COMMENT 'Current activation level',
    threshold DECIMAL(5,2) NULL COMMENT 'Threshold for completion',

    -- Matched components
    matchedComponents JSON NULL COMMENT 'Array of matched tokens',

    -- MWE lookahead tracking
    lookaheadCounter SMALLINT DEFAULT 0 COMMENT 'Number of lookahead checks performed',
    invalidationReason TEXT NULL COMMENT 'Why MWE was invalidated (if applicable)',

    -- Timestamp
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Indexes
    INDEX idx_alt_graph (idParserGraph),
    INDEX idx_alt_status (status),
    INDEX idx_alt_type (idParserGraph, constructionType),
    INDEX idx_alt_position (idParserGraph, startPosition),

    FOREIGN KEY (idParserGraph) REFERENCES parser_graph(idParserGraph) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Debug log tracking construction alternatives during parsing (V4)';

-- ============================================================================
-- Utility Views
-- ============================================================================

-- View for construction statistics
CREATE OR REPLACE VIEW view_parser_construction_stats AS
SELECT
    gg.idGrammarGraph,
    gg.name AS grammarName,
    gg.language,
    COUNT(DISTINCT c.idConstruction) AS totalConstructions,
    SUM(CASE WHEN c.constructionType = 'mwe' THEN 1 ELSE 0 END) AS mweCount,
    SUM(CASE WHEN c.constructionType = 'phrasal' THEN 1 ELSE 0 END) AS phrasalCount,
    SUM(CASE WHEN c.constructionType = 'clausal' THEN 1 ELSE 0 END) AS clausalCount,
    SUM(CASE WHEN c.constructionType = 'sentential' THEN 1 ELSE 0 END) AS sententialCount,
    SUM(CASE WHEN c.enabled = TRUE THEN 1 ELSE 0 END) AS enabledCount
FROM parser_grammar_graph gg
LEFT JOIN parser_construction_v4 c ON gg.idGrammarGraph = c.idGrammarGraph
GROUP BY gg.idGrammarGraph, gg.name, gg.language;

-- View for parse graph statistics with multi-level CE counts
CREATE OR REPLACE VIEW view_parser_graph_stats_v4 AS
SELECT
    pg.idParserGraph,
    pg.sentence,
    pg.status,
    gg.name AS grammarName,
    COUNT(DISTINCT pn.idParserNode) AS nodeCount,
    COUNT(DISTINCT pl.idParserLink) AS linkCount,
    SUM(CASE WHEN pn.isFocus = TRUE THEN 1 ELSE 0 END) AS focusNodeCount,
    SUM(CASE WHEN pn.type = 'MWE' THEN 1 ELSE 0 END) AS mweNodeCount,
    SUM(CASE WHEN pn.phrasalCE IS NOT NULL THEN 1 ELSE 0 END) AS phrasalCECount,
    SUM(CASE WHEN pn.clausalCE IS NOT NULL THEN 1 ELSE 0 END) AS clausalCECount,
    SUM(CASE WHEN pn.sententialCE IS NOT NULL THEN 1 ELSE 0 END) AS sententialCECount,
    COUNT(DISTINCT pn.constructionId) AS distinctConstructionsUsed
FROM parser_graph pg
LEFT JOIN parser_grammar_graph gg ON pg.idGrammarGraph = gg.idGrammarGraph
LEFT JOIN parser_node pn ON pg.idParserGraph = pn.idParserGraph
LEFT JOIN parser_link pl ON pg.idParserGraph = pl.idParserGraph
GROUP BY pg.idParserGraph, pg.sentence, pg.status, gg.name;

-- View for alternative log analysis
CREATE OR REPLACE VIEW view_parser_alternative_stats AS
SELECT
    pg.idParserGraph,
    pg.sentence,
    COUNT(DISTINCT al.idAlternativeLog) AS totalAlternatives,
    SUM(CASE WHEN al.status = 'confirmed' THEN 1 ELSE 0 END) AS confirmedCount,
    SUM(CASE WHEN al.status = 'invalidated' THEN 1 ELSE 0 END) AS invalidatedCount,
    SUM(CASE WHEN al.status = 'abandoned' THEN 1 ELSE 0 END) AS abandonedCount,
    SUM(CASE WHEN al.status = 'aggregated' THEN 1 ELSE 0 END) AS aggregatedCount,
    SUM(CASE WHEN al.constructionType = 'mwe' THEN 1 ELSE 0 END) AS mweAlternatives,
    SUM(CASE WHEN al.constructionType = 'phrasal' THEN 1 ELSE 0 END) AS phrasalAlternatives,
    SUM(CASE WHEN al.constructionType = 'clausal' THEN 1 ELSE 0 END) AS clausalAlternatives,
    SUM(CASE WHEN al.constructionType = 'sentential' THEN 1 ELSE 0 END) AS sententialAlternatives
FROM parser_graph pg
LEFT JOIN parser_alternative_log al ON pg.idParserGraph = al.idParserGraph
GROUP BY pg.idParserGraph, pg.sentence;

-- ============================================================================
-- Sample Data: Portuguese Grammar (V4)
-- ============================================================================

-- Insert base Portuguese grammar graph
INSERT IGNORE INTO parser_grammar_graph (idGrammarGraph, name, language, description) VALUES
(1, 'Portuguese V4 Grammar', 'pt', 'Portuguese unified constructional grammar with multi-level CE labels');

-- ============================================================================
-- Migration Notes
-- ============================================================================

-- To migrate from old schema to V4:
--
-- 1. Migrate parser_mwe to parser_construction_v4:
--    INSERT INTO parser_construction_v4 (
--        idGrammarGraph, name, constructionType, pattern, priority,
--        phrasalCE, aggregateAs, semanticType, enabled, lookaheadEnabled
--    )
--    SELECT
--        idGrammarGraph,
--        phrase AS name,
--        'mwe' AS constructionType,
--        -- Convert JSON array to BNF pattern
--        CONCAT('"', REPLACE(REPLACE(REPLACE(
--            JSON_EXTRACT(components, '$'),
--            '[', ''), ']', ''), '","', '" "'), '"') AS pattern,
--        150 AS priority,
--        semanticType AS phrasalCE,
--        phrase AS aggregateAs,
--        semanticType,
--        TRUE AS enabled,
--        FALSE AS lookaheadEnabled
--    FROM parser_mwe
--    WHERE components IS NOT NULL;
--
-- 2. After successful migration and V4 testing:
--    - Backup old tables: CREATE TABLE parser_mwe_backup AS SELECT * FROM parser_mwe;
--    - Drop obsolete tables: DROP TABLE parser_mwe, parser_grammar_node, parser_grammar_link;
--
-- 3. The parser_node modifications are handled by migration:
--    - 2025_12_10_223819_add_multilevel_ce_to_parser_node.php

-- ============================================================================
-- End of V4 Schema
-- ============================================================================
