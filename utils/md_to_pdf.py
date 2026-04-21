#!/usr/bin/env python3
"""
Convert markdown file to PDF with formatting.

Usage:
    python md_to_pdf.py input.md output.pdf
"""

from reportlab.lib.pagesizes import letter
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import inch
from reportlab.lib.enums import TA_LEFT, TA_CENTER
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, PageBreak, Table, TableStyle, Preformatted
from reportlab.lib import colors
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
import re
import sys

def parse_markdown_to_pdf(md_file, pdf_file):
    """Convert markdown to PDF with formatting."""
    
    # Read markdown content
    with open(md_file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Create PDF document
    doc = SimpleDocTemplate(
        pdf_file,
        pagesize=letter,
        rightMargin=0.75*inch,
        leftMargin=0.75*inch,
        topMargin=0.75*inch,
        bottomMargin=0.75*inch
    )
    
    # Define styles
    styles = getSampleStyleSheet()
    
    # Custom styles
    title_style = ParagraphStyle(
        'CustomTitle',
        parent=styles['Heading1'],
        fontSize=18,
        textColor=colors.HexColor('#1a1a1a'),
        spaceAfter=12,
        spaceBefore=0,
        leftIndent=0,
        fontName='Helvetica-Bold'
    )
    
    heading1_style = ParagraphStyle(
        'CustomHeading1',
        parent=styles['Heading1'],
        fontSize=16,
        textColor=colors.HexColor('#2c3e50'),
        spaceAfter=10,
        spaceBefore=16,
        fontName='Helvetica-Bold'
    )
    
    heading2_style = ParagraphStyle(
        'CustomHeading2',
        parent=styles['Heading2'],
        fontSize=14,
        textColor=colors.HexColor('#34495e'),
        spaceAfter=8,
        spaceBefore=12,
        fontName='Helvetica-Bold'
    )
    
    heading3_style = ParagraphStyle(
        'CustomHeading3',
        parent=styles['Heading3'],
        fontSize=12,
        textColor=colors.HexColor('#555555'),
        spaceAfter=6,
        spaceBefore=10,
        fontName='Helvetica-Bold'
    )
    
    heading4_style = ParagraphStyle(
        'CustomHeading4',
        parent=styles['Heading3'],
        fontSize=11,
        textColor=colors.HexColor('#666666'),
        spaceAfter=6,
        spaceBefore=8,
        fontName='Helvetica-Bold'
    )
    
    body_style = ParagraphStyle(
        'CustomBody',
        parent=styles['Normal'],
        fontSize=10,
        textColor=colors.HexColor('#333333'),
        spaceAfter=6,
        leading=14,
        fontName='Helvetica'
    )
    
    code_style = ParagraphStyle(
        'Code',
        parent=styles['Code'],
        fontSize=8,
        textColor=colors.HexColor('#2c3e50'),
        backColor=colors.HexColor('#f5f5f5'),
        leftIndent=20,
        rightIndent=20,
        spaceAfter=8,
        spaceBefore=8,
        fontName='Courier'
    )
    
    story = []
    
    # Split content into lines
    lines = content.split('\n')
    i = 0
    in_code_block = False
    code_lines = []
    in_table = False
    table_lines = []
    
    while i < len(lines):
        line = lines[i]
        
        # Handle code blocks
        if line.strip().startswith('```'):
            if not in_code_block:
                in_code_block = True
                code_lines = []
            else:
                in_code_block = False
                # Add code block to story
                if code_lines:
                    code_text = '\n'.join(code_lines)
                    # Wrap long lines
                    wrapped_lines = []
                    for code_line in code_lines:
                        if len(code_line) > 85:
                            # Break long lines
                            while len(code_line) > 85:
                                wrapped_lines.append(code_line[:85])
                                code_line = '  ' + code_line[85:]
                            if code_line:
                                wrapped_lines.append(code_line)
                        else:
                            wrapped_lines.append(code_line)
                    code_text = '\n'.join(wrapped_lines)
                    pre = Preformatted(code_text, code_style)
                    story.append(pre)
                    story.append(Spacer(1, 6))
            i += 1
            continue
        
        if in_code_block:
            code_lines.append(line)
            i += 1
            continue
        
        # Handle tables
        if line.strip().startswith('|') and not in_table:
            in_table = True
            table_lines = [line]
            i += 1
            continue
        
        if in_table:
            if line.strip().startswith('|'):
                table_lines.append(line)
                i += 1
                continue
            else:
                # End of table
                in_table = False
                # Process table
                if len(table_lines) > 2:  # Header + separator + at least one row
                    table_data = []
                    for table_line in table_lines:
                        if '|--' not in table_line:  # Skip separator line
                            cells = [cell.strip() for cell in table_line.split('|')[1:-1]]
                            table_data.append(cells)
                    
                    if table_data:
                        # Calculate available width
                        available_width = doc.width
                        num_cols = len(table_data[0])
                        
                        # Create style for table cells
                        table_cell_style = ParagraphStyle(
                            'TableCell',
                            parent=styles['Normal'],
                            fontSize=7,
                            leading=9,
                            textColor=colors.HexColor('#333333'),
                            fontName='Helvetica'
                        )
                        
                        table_header_style = ParagraphStyle(
                            'TableHeader',
                            parent=styles['Normal'],
                            fontSize=8,
                            leading=10,
                            textColor=colors.whitesmoke,
                            fontName='Helvetica-Bold'
                        )
                        
                        # Convert cells to Paragraphs for wrapping
                        wrapped_data = []
                        for row_idx, row in enumerate(table_data):
                            wrapped_row = []
                            for cell in row:
                                # Replace bullet characters and line breaks
                                cell_text = cell.replace('•', '&#8226;')  # Use HTML entity for bullet
                                cell_text = cell_text.replace('<br>', ' ')  # Replace line breaks with spaces
                                cell_text = cell_text.replace('\n', ' ')  # Replace newlines with spaces
                                
                                # Handle bold text
                                cell_text = re.sub(r'\*\*(.*?)\*\*', r'<b>\1</b>', cell_text)
                                # Handle inline code
                                cell_text = re.sub(r'`(.*?)`', r'<font face="Courier" size="7">\1</font>', cell_text)
                                
                                if row_idx == 0:  # Header row
                                    wrapped_row.append(Paragraph(cell_text, table_header_style))
                                else:
                                    wrapped_row.append(Paragraph(cell_text, table_cell_style))
                            wrapped_data.append(wrapped_row)
                        
                        # Calculate column widths - distribute evenly but with min/max constraints
                        col_width = available_width / num_cols
                        col_widths = [col_width] * num_cols
                        
                        # Create table with styling and column widths
                        t = Table(wrapped_data, colWidths=col_widths)
                        t.setStyle(TableStyle([
                            ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor('#3498db')),
                            ('TEXTCOLOR', (0, 0), (-1, 0), colors.whitesmoke),
                            ('ALIGN', (0, 0), (-1, -1), 'LEFT'),
                            ('VALIGN', (0, 0), (-1, -1), 'TOP'),
                            ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
                            ('FONTSIZE', (0, 0), (-1, 0), 8),
                            ('FONTSIZE', (0, 1), (-1, -1), 7),
                            ('BOTTOMPADDING', (0, 0), (-1, 0), 6),
                            ('TOPPADDING', (0, 0), (-1, -1), 4),
                            ('BOTTOMPADDING', (0, 1), (-1, -1), 4),
                            ('LEFTPADDING', (0, 0), (-1, -1), 4),
                            ('RIGHTPADDING', (0, 0), (-1, -1), 4),
                            ('BACKGROUND', (0, 1), (-1, -1), colors.white),
                            ('GRID', (0, 0), (-1, -1), 0.5, colors.grey),
                            ('ROWBACKGROUNDS', (0, 1), (-1, -1), [colors.white, colors.HexColor('#f9f9f9')]),
                        ]))
                        story.append(t)
                        story.append(Spacer(1, 12))
                table_lines = []
                # Don't increment i, process current line
                continue
        
        # Handle headings
        if line.startswith('# '):
            text = line[2:].strip()
            text = re.sub(r'\*\*(.*?)\*\*', r'<b>\1</b>', text)
            story.append(Paragraph(text, title_style))
        elif line.startswith('## '):
            text = line[3:].strip()
            text = re.sub(r'\*\*(.*?)\*\*', r'<b>\1</b>', text)
            story.append(Paragraph(text, heading1_style))
        elif line.startswith('### '):
            text = line[4:].strip()
            text = re.sub(r'\*\*(.*?)\*\*', r'<b>\1</b>', text)
            story.append(Paragraph(text, heading2_style))
        elif line.startswith('#### '):
            text = line[5:].strip()
            text = re.sub(r'\*\*(.*?)\*\*', r'<b>\1</b>', text)
            story.append(Paragraph(text, heading3_style))
        elif line.startswith('##### '):
            text = line[6:].strip()
            text = re.sub(r'\*\*(.*?)\*\*', r'<b>\1</b>', text)
            story.append(Paragraph(text, heading4_style))
        
        # Handle horizontal rules
        elif line.strip() == '---':
            story.append(Spacer(1, 8))
            from reportlab.platypus import HRFlowable
            story.append(HRFlowable(width="100%", thickness=1, color=colors.grey, 
                                   spaceBefore=4, spaceAfter=4))
            story.append(Spacer(1, 8))
        
        # Handle list items
        elif line.strip().startswith('- ') or line.strip().startswith('* '):
            text = line.strip()[2:]
            # Handle bold text
            text = re.sub(r'\*\*(.*?)\*\*', r'<b>\1</b>', text)
            # Handle inline code
            text = re.sub(r'`(.*?)`', r'<font face="Courier" size="9">\1</font>', text)
            bullet_text = f"• {text}"
            story.append(Paragraph(bullet_text, body_style))
        
        # Handle numbered lists
        elif re.match(r'^\d+\.', line.strip()):
            text = re.sub(r'^\d+\.\s+', '', line.strip())
            # Handle bold text
            text = re.sub(r'\*\*(.*?)\*\*', r'<b>\1</b>', text)
            # Handle inline code
            text = re.sub(r'`(.*?)`', r'<font face="Courier" size="9">\1</font>', text)
            # Extract number
            num = line.strip().split('.')[0]
            numbered_text = f"{num}. {text}"
            story.append(Paragraph(numbered_text, body_style))
        
        # Handle regular paragraphs
        elif line.strip():
            text = line.strip()
            # Handle bold text
            text = re.sub(r'\*\*(.*?)\*\*', r'<b>\1</b>', text)
            # Handle inline code
            text = re.sub(r'`(.*?)`', r'<font face="Courier" size="9">\1</font>', text)
            # Handle links [text](url) - just show text
            text = re.sub(r'\[(.*?)\]\(.*?\)', r'\1', text)
            
            story.append(Paragraph(text, body_style))
        
        # Empty line
        else:
            if story and not isinstance(story[-1], Spacer):
                story.append(Spacer(1, 6))
        
        i += 1
    
    # Build PDF
    doc.build(story)
    print(f"PDF created successfully: {pdf_file}")

if __name__ == '__main__':
    if len(sys.argv) != 3:
        print("Usage: python md_to_pdf.py input.md output.pdf")
        sys.exit(1)
    
    input_file = sys.argv[1]
    output_file = sys.argv[2]
    
    parse_markdown_to_pdf(input_file, output_file)
