import os
import re

def simplify_docblocks(directory):
    count = 0
    # Match /** ... */
    pattern = re.compile(r'^[ \t]*/\*\*([\s\S]*?)\*/', re.MULTILINE)
    
    for root, dirs, files in os.walk(directory):
        if any(ignored in root for ignored in ['vendor', 'node_modules', '.git', 'storage', 'bootstrap/cache']):
            continue
            
        for file in files:
            if file.endswith('.php'):
                filepath = os.path.join(root, file)
                try:
                    with open(filepath, 'r', encoding='utf-8') as f:
                        content = f.read()
                        
                    original = content
                    
                    def replacer(match):
                        block = match.group(0)
                        inner = match.group(1)
                        lines = inner.split('\n')
                        text_lines = []
                        for line in lines:
                            cleaned = line.strip().strip('*').strip()
                            if cleaned and not cleaned.startswith('@'):
                                text_lines.append(cleaned)
                        
                        # Only simplify if it's a short text, say 1 or 2 lines
                        if len(text_lines) == 1 or len(text_lines) == 2:
                            # Also check if it's not a complex class-level block with a lot of annotations
                            if len(block.split('\n')) < 15:
                                indent = match.group(0)[:match.group(0).find('/')]
                                return indent + '// ' + ' '.join(text_lines)
                        
                        # Also replace empty ones or those with just one @var
                        if len(text_lines) == 0 and inner.count('@') == 1:
                             # Just remove it or keep as single line? We can keep the @var text if we want, but usually it's obvious
                             # e.g. /** @use HasFactory... */
                             pass
                             
                        return match.group(0)

                    content = pattern.sub(replacer, content)
                    
                    if original != content:
                        with open(filepath, 'w', encoding='utf-8') as f:
                            f.write(content)
                        count += 1
                        print(f"Simplified docblocks in {filepath}")
                except Exception as e:
                    print(f"Error processing {filepath}: {e}")
                    
    print(f"Processed {count} files.")

simplify_docblocks('.')
