-- ============================================================================
-- Parser V4 Minimal Schema: Essential Tables Only (No History/Persistence)
-- ============================================================================
-- This is the MINIMAL schema needed for V4 parser to function.
-- No parse history is stored - parser returns results in-memory (ParseStateV4).
-- Use this for initial testing and development.
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Grammar Configuration (REQUIRED)
-- ----------------------------------------------------------------------------

-- Grammar graphs define language-specific parsing configurations
CREATE TABLE IF NOT EXISTS parser_grammar_graph (
    idGrammarGraph INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL COMMENT 'Grammar name (e.g., "Portuguese V4 Grammar")',
    language VARCHAR(10) NOT NULL COMMENT 'ISO language code (e.g., "pt", "en")',
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_language (language)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Grammar configurations for different languages';

-- ----------------------------------------------------------------------------
-- Unified Construction Registry (REQUIRED - THE CORE OF V4)
-- ----------------------------------------------------------------------------

-- All construction patterns (MWE, phrasal, clausal, sentential) in one table
-- The parser LOADS these at runtime to match against tokens
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
COMMENT='Unified construction registry - THE CORE DATA FOR V4 PARSER';

-- ----------------------------------------------------------------------------
-- Debugging and Analysis (OPTIONAL - Can be removed for initial testing)
-- ----------------------------------------------------------------------------

-- Alternative log tracks construction alternatives during incremental parsing
-- OPTIONAL: Only enable if you need to debug alternative selection
CREATE TABLE IF NOT EXISTS parser_alternative_log (
    idAlternativeLog BIGINT AUTO_INCREMENT PRIMARY KEY,
    idGrammarGraph INT NOT NULL COMMENT 'Which grammar was used',

    -- Sentence context (for reference, not required for parsing)
    sentenceText TEXT NULL COMMENT 'Optional: the sentence being parsed',

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
    INDEX idx_alt_grammar (idGrammarGraph),
    INDEX idx_alt_status (status),
    INDEX idx_alt_type (idGrammarGraph, constructionType),
    INDEX idx_alt_position (startPosition),

    FOREIGN KEY (idGrammarGraph) REFERENCES parser_grammar_graph(idGrammarGraph) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='OPTIONAL: Debug log tracking construction alternatives during parsing';

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

-- ============================================================================
-- Sample Data: Portuguese Grammar (V4)
-- ============================================================================

-- Insert base Portuguese grammar graph
INSERT IGNORE INTO parser_grammar_graph (idGrammarGraph, name, language, description) VALUES
(1, 'Portuguese V4 Grammar', 'pt', 'Portuguese unified constructional grammar - minimal schema for testing');

-- ============================================================================
-- V4 Parser Operation
-- ============================================================================

-- HOW V4 PARSER WORKS WITH THIS SCHEMA:
--
-- 1. LOAD PHASE (startup/initialization):
--    - ConstructionRegistry.loadConstructions(idGrammarGraph)
--    - Loads all constructions from parser_construction_v4 into memory
--    - Cached for performance
--
-- 2. PARSE PHASE (runtime - all in-memory):
--    - IncrementalParserEngine.parse(tokens, idGrammarGraph)
--    - Creates ParseStateV4 (in-memory)
--    - Processes token by token
--    - Returns ParseStateV4 with:
--      * nodes: array of node objects
--      * edges: array of edge objects
--      * alternatives: active alternatives
--      * consumedPositions: which tokens were consumed
--
-- 3. OUTPUT PHASE (application decides what to do):
--    - Application receives ParseStateV4
--    - Can extract CE labels, dependencies, MWE aggregations
--    - Can convert to annotation format
--    - Can persist if needed (but not required!)
--
-- NO PERSISTENCE REQUIRED DURING PARSING!

-- ============================================================================
-- Migration Notes
-- ============================================================================

-- To populate parser_construction_v4 from old parser_mwe table:
--
-- INSERT INTO parser_construction_v4 (
--     idGrammarGraph, name, constructionType, pattern, priority,
--     phrasalCE, aggregateAs, semanticType, enabled, lookaheadEnabled
-- )
-- SELECT
--     idGrammarGraph,
--     phrase AS name,
--     'mwe' AS constructionType,
--     -- Convert JSON array to BNF pattern: ["café","da","manhã"] -> "café" "da" "manhã"
--     CONCAT('"',
--         REPLACE(
--             REPLACE(
--                 REPLACE(JSON_EXTRACT(components, '$'), '[', ''),
--                 ']', ''
--             ),
--             '","', '" "'
--         ),
--         '"'
--     ) AS pattern,
--     150 AS priority,
--     semanticType AS phrasalCE,
--     phrase AS aggregateAs,
--     semanticType,
--     TRUE AS enabled,
--     FALSE AS lookaheadEnabled
-- FROM parser_mwe
-- WHERE components IS NOT NULL;

-- ============================================================================
-- End of Minimal V4 Schema
-- ============================================================================
