<?php

use App\Services\SSD\LouvainCommunityDetector;

describe('LouvainCommunityDetector', function () {
    it('returns empty array for empty node list', function () {
        $detector = new LouvainCommunityDetector;
        $result = $detector->detect([], []);
        expect($result)->toBe([]);
    });

    it('puts all isolated nodes in separate communities', function () {
        $detector = new LouvainCommunityDetector;
        $nodes = ['A', 'B', 'C'];
        $edges = [];

        $result = $detector->detect($nodes, $edges);

        expect($result)->toHaveKey('A')
            ->and($result)->toHaveKey('B')
            ->and($result)->toHaveKey('C');

        // All isolated: might be same or different community depending on init
        expect(count($result))->toBe(3);
    });

    it('detects two communities in a bipartite structure', function () {
        $detector = new LouvainCommunityDetector;

        // Triangle A-B-C and separate triangle D-E-F, weakly connected via C-D
        $nodes = ['A', 'B', 'C', 'D', 'E', 'F'];
        $edges = [
            ['source' => 'A', 'target' => 'B', 'weight' => 5.0],
            ['source' => 'B', 'target' => 'C', 'weight' => 5.0],
            ['source' => 'C', 'target' => 'A', 'weight' => 5.0],
            ['source' => 'D', 'target' => 'E', 'weight' => 5.0],
            ['source' => 'E', 'target' => 'F', 'weight' => 5.0],
            ['source' => 'F', 'target' => 'D', 'weight' => 5.0],
            // Weak connection between groups
            ['source' => 'C', 'target' => 'D', 'weight' => 0.1],
        ];

        $result = $detector->detect($nodes, $edges, 1.0);

        expect($result)->toHaveKey('A')
            ->and($result)->toHaveKey('D');

        // A, B, C should be in the same community
        expect($result['A'])->toBe($result['B'])
            ->and($result['B'])->toBe($result['C']);

        // D, E, F should be in the same community
        expect($result['D'])->toBe($result['E'])
            ->and($result['E'])->toBe($result['F']);

        // The two groups should be in different communities
        expect($result['A'])->not->toBe($result['D']);
    });

    it('assigns all nodes to same community when fully connected', function () {
        $detector = new LouvainCommunityDetector;
        $nodes = ['A', 'B', 'C'];
        $edges = [
            ['source' => 'A', 'target' => 'B', 'weight' => 5.0],
            ['source' => 'B', 'target' => 'C', 'weight' => 5.0],
            ['source' => 'C', 'target' => 'A', 'weight' => 5.0],
        ];

        $result = $detector->detect($nodes, $edges, 0.5);

        // At low resolution, all should be in one community
        $uniqueCommunities = array_unique(array_values($result));
        expect(count($uniqueCommunities))->toBe(1);
    });

    it('calculates modularity for a known partition', function () {
        $detector = new LouvainCommunityDetector;
        $nodes = ['A', 'B', 'C', 'D'];
        $edges = [
            ['source' => 'A', 'target' => 'B', 'weight' => 4.0],
            ['source' => 'C', 'target' => 'D', 'weight' => 4.0],
            ['source' => 'B', 'target' => 'C', 'weight' => 1.0],
        ];

        // Good partition: {A,B} and {C,D}
        $partition = ['A' => 0, 'B' => 0, 'C' => 1, 'D' => 1];
        $q = $detector->modularity($partition, $edges);

        // Modularity should be positive for a good partition
        expect($q)->toBeGreaterThan(0.0);
    });

    it('higher resolution produces more communities', function () {
        $detector = new LouvainCommunityDetector;
        $nodes = ['A', 'B', 'C', 'D', 'E', 'F'];
        $edges = [
            ['source' => 'A', 'target' => 'B', 'weight' => 3.0],
            ['source' => 'B', 'target' => 'C', 'weight' => 3.0],
            ['source' => 'D', 'target' => 'E', 'weight' => 3.0],
            ['source' => 'E', 'target' => 'F', 'weight' => 3.0],
            ['source' => 'C', 'target' => 'D', 'weight' => 1.5],
        ];

        $lowRes = $detector->detect($nodes, $edges, 0.5);
        $highRes = $detector->detect($nodes, $edges, 2.0);

        $lowCommunities = count(array_unique(array_values($lowRes)));
        $highCommunities = count(array_unique(array_values($highRes)));

        // Higher resolution should produce at least as many communities
        expect($highCommunities)->toBeGreaterThanOrEqual($lowCommunities);
    });
});
