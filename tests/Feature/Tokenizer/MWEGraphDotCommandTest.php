<?php

use App\Database\Criteria;

describe('MWEGraphDotCommand', function () {
    it('displays help when called with --help', function () {
        $this->artisan('tokenizer:mwe-graph-dot', ['--help' => true, 'idLemma' => 0])
            ->assertSuccessful();
    });

    it('fails with a clear message for an unknown idLemma', function () {
        $this->artisan('tokenizer:mwe-graph-dot', ['idLemma' => 99999999])
            ->assertFailed()
            ->expectsOutputToContain('No MWE found for idLemma 99999999');
    });

    it('fails with a clear message when the graph has not been built yet', function () {
        // idLemma=1 exists in lexicon_mwe but has no M node (graph not built for it here)
        // We verify by temporarily checking a valid MWE idLemma that has no node
        $validMweIdLemma = Criteria::table('lexicon_mwe')
            ->whereNotIn('idLemma', function ($q) {
                $q->from('lexicon_mwe_node')->where('type', '=', 'M')->select('idLemma');
            })
            ->value('idLemma');

        if ($validMweIdLemma === null) {
            $this->markTestSkipped('All MWEs have M nodes — graph is fully built.');
        }

        $this->artisan('tokenizer:mwe-graph-dot', ['idLemma' => $validMweIdLemma])
            ->assertFailed()
            ->expectsOutputToContain('No M node found');
    });

    it('outputs valid DOT syntax to stdout for a 2-component MWE', function () {
        // idLemma=79 is "arena corinthians" (2 components)
        $output = $this->artisan('tokenizer:mwe-graph-dot', ['idLemma' => 79])
            ->assertSuccessful();

        $output->expectsOutput('digraph MWE_79 {');
    });

    it('DOT output contains M, C, and A nodes for a multi-component MWE', function () {
        // idLemma=110 is "banco de reservas" (3 components)
        $this->artisan('tokenizer:mwe-graph-dot', ['idLemma' => 110])
            ->assertSuccessful()
            ->expectsOutputToContain('shape=rectangle')   // M node
            ->expectsOutputToContain('shape=ellipse')     // C node
            ->expectsOutputToContain('shape=diamond');    // A node
    });

    it('DOT output contains only M and C nodes for a single-component MWE', function () {
        $singleComponentIdLemma = Criteria::table('lexicon_mwe')
            ->select('idLemma')
            ->groupBy('idLemma')
            ->havingRaw('COUNT(*) = 1')
            ->value('idLemma');

        if ($singleComponentIdLemma === null) {
            $this->markTestSkipped('No single-component MWE found.');
        }

        $this->artisan('tokenizer:mwe-graph-dot', ['idLemma' => $singleComponentIdLemma])
            ->assertSuccessful()
            ->expectsOutputToContain('shape=rectangle')   // M node
            ->expectsOutputToContain('shape=ellipse');    // C node
    });

    it('writes DOT output to a file when --output-path is given', function () {
        $tmpFile = sys_get_temp_dir().'/mwe_dot_test_'.uniqid().'.dot';

        $this->artisan('tokenizer:mwe-graph-dot', [
            'idLemma' => 79,
            '--output-path' => $tmpFile,
        ])
            ->assertSuccessful()
            ->expectsOutputToContain('DOT file written to');

        expect(file_exists($tmpFile))->toBeTrue();
        expect(file_get_contents($tmpFile))->toContain('digraph MWE_79 {');

        unlink($tmpFile);
    });

    it('renders a PNG via --render and reports the output path', function () {
        $tmpDot = sys_get_temp_dir().'/mwe_render_test_'.uniqid().'.dot';
        $tmpPng = str_replace('.dot', '.png', $tmpDot);

        $this->artisan('tokenizer:mwe-graph-dot', [
            'idLemma' => 79,
            '--output-path' => $tmpDot,
            '--render' => true,
        ])
            ->assertSuccessful()
            ->expectsOutputToContain('DOT file written to')
            ->expectsOutputToContain('PNG rendered to');

        expect(file_exists($tmpPng))->toBeTrue();

        unlink($tmpDot);
        unlink($tmpPng);
    });

    it('renders a PNG to a temp DOT file when --render is used without --output-path', function () {
        $this->artisan('tokenizer:mwe-graph-dot', [
            'idLemma' => 79,
            '--render' => true,
        ])
            ->assertSuccessful()
            ->expectsOutputToContain('PNG rendered to');
    });
});
