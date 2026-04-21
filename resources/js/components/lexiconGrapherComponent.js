/**
 * Alpine.js Lexicon Grapher Component
 * Renders lexicon graphs (lemma/form nodes) using JointJS library
 */

export default function lexiconGrapherComponent(config = {}) {
    return {
        nodes: config.nodes || {},
        links: config.links || {},

        connector: 'normal',
        ranker: 'network-simplex',
        rankdir: 'LR',
        align: 'DL',
        vertices: false,
        ranksep: 60,
        edgesep: 20,
        nodesep: 30,

        graph: null,
        paper: null,
        panAndZoom: null,
        initialized: false,
        hoverTimer: null,

        init() {
            if (this.initialized) {
                return;
            }
            if (typeof joint === 'undefined') {
                console.error('Lexicon Grapher: JointJS library not found');
                return;
            }
            this.$nextTick(() => {
                this.initializeGraph();
                this.layout();
                this.initialized = true;
            });
        },

        initializeGraph() {
            this.graph = new joint.dia.Graph();

            const graphEl = this.$el.querySelector('#graph') || this.$el;
            const containerWidth = graphEl.clientWidth || 800;
            const containerHeight = graphEl.clientHeight || 600;

            this.paper = new joint.dia.Paper({
                el: graphEl,
                model: this.graph,
                width: containerWidth,
                height: containerHeight,
                gridSize: 1,
                background: { color: '#ffffff' },
                interactive: true,
            });

            this.paper.on('blank:pointerdown', () => {
                if (this.panAndZoom) {
                    this.panAndZoom.enablePan();
                }
            });
            this.paper.on('cell:pointerdown', () => {
                if (this.panAndZoom) {
                    this.panAndZoom.disablePan();
                }
            });
            this.paper.on('cell:pointerup blank:pointerup', () => {
                if (this.panAndZoom) {
                    this.panAndZoom.disablePan();
                }
            });
            this.paper.on('element:mouseenter', (elementView) => this.elementEnter(elementView));
            this.paper.on('element:mouseleave', (elementView) => this.elementLeave(elementView));

            this.buildGraph();
        },

        buildGraph() {
            const elements = [];
            const links = [];

            for (const id in this.nodes) {
                const node = this.nodes[id];
                const text = node.name;
                let shape;

                if (node.type === 'lemma') {
                    const w = Math.max(text.length * 9, 80);
                    shape = new joint.shapes.standard.Rectangle({ id, z: 2 });
                    shape.resize(w, 40);
                    shape.attr({
                        body: { fill: '#3B82F6', stroke: '#1D4ED8', strokeWidth: 2 },
                        label: { fill: '#ffffff', text, fontWeight: 'bold', fontSize: 13 },
                    });
                } else if (node.type === 'form') {
                    const w = Math.max(text.length * 8, 70);
                    shape = new joint.shapes.standard.Ellipse({ id, z: 2 });
                    shape.resize(w, 36);
                    shape.attr({
                        body: { fill: '#F3F4F6', stroke: '#9CA3AF', strokeWidth: 1 },
                        label: { fill: '#374151', text, fontSize: 12 },
                    });
                }

                if (shape) {
                    elements.push(shape);
                }
            }

            for (const source in this.links) {
                for (const target in this.links[source]) {
                    const link = new joint.shapes.standard.Link({
                        source: { id: source },
                        target: { id: target },
                        attrs: {
                            line: {
                                stroke: '#6B7280',
                                strokeWidth: 1.5,
                                targetMarker: {
                                    type: 'path',
                                    d: 'M 8 -3 0 0 8 3 z',
                                    fill: '#6B7280',
                                    stroke: '#6B7280',
                                },
                            },
                        },
                    });
                    link.connector(this.connector);
                    links.push(link);
                }
            }

            this.graph.resetCells(elements.concat(links));
        },

        layout() {
            if (!this.graph || typeof dagre === 'undefined') {
                return;
            }

            joint.layout.DirectedGraph.layout(this.graph, {
                dagre,
                graphlib: dagre.graphlib,
                nodeSep: this.nodesep,
                edgeSep: this.edgesep,
                rankSep: this.ranksep,
                rankDir: this.rankdir,
                align: this.align,
                ranker: this.ranker,
            });

            this.paper.scaleContentToFit({ padding: 20, maxScale: 1 });

            if (typeof svgPanZoom !== 'undefined' && this.paper.svg) {
                if (this.panAndZoom) {
                    this.panAndZoom.destroy();
                }
                this.panAndZoom = svgPanZoom(this.paper.svg, {
                    zoomEnabled: true,
                    controlIconsEnabled: true,
                    dblClickZoomEnabled: false,
                    fit: false,
                    center: false,
                });
                this.panAndZoom.enableControlIcons();
                this.panAndZoom.disablePan();
            }
        },

        elementEnter(elementView) {
            if (this.hoverTimer) {
                clearTimeout(this.hoverTimer);
            }
            this.hoverTimer = setTimeout(() => {
                const removeButton = new joint.elementTools.Remove({
                    offset: { x: 0, y: 0 },
                    markup: [
                        {
                            tagName: 'circle',
                            selector: 'button',
                            attributes: { r: 7, fill: '#FF6B6B', cursor: 'pointer' },
                        },
                        {
                            tagName: 'path',
                            selector: 'icon',
                            attributes: {
                                d: 'M -3 -3 3 3 M -3 3 3 -3',
                                fill: 'none',
                                stroke: '#FFFFFF',
                                'stroke-width': 2,
                                'pointer-events': 'none',
                            },
                        },
                    ],
                });
                elementView.addTools(new joint.dia.ToolsView({ tools: [removeButton] }));
                elementView.showTools();
            }, 300);
        },

        elementLeave(elementView) {
            if (this.hoverTimer) {
                clearTimeout(this.hoverTimer);
                this.hoverTimer = null;
            }
            elementView.hideTools();
        },

        updateData(newNodes, newLinks) {
            this.nodes = newNodes || {};
            this.links = newLinks || {};

            const graphEl = this.$el.querySelector('#graph') || this.$el;
            const paperStillExists = graphEl && graphEl.querySelector('svg');

            if (this.initialized && !paperStillExists) {
                this.initializeGraph();
                this.layout();
            } else if (this.graph && this.initialized) {
                this.buildGraph();
                this.layout();
            }
        },
    };
}
