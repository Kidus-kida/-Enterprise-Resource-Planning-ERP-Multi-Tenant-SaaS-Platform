#!/usr/bin/env python3
import re
import sys
import os

def convert_form_label(match):
    field = match.group(1)
    label_text = match.group(2)
    return f'<label for="{field}">{{{{{ {label_text} }}}}</label>'

def convert_form_text(content):
    # Pattern: Form::text('field', value, [attrs])
    pattern = r'''\{!! Form::text\('([^']+)',\s*([^,]+),\s*\[(.*?)\]\);?\s*!!\}'''
    def replacer(match):
        field = match.group(1)
        value = match.group(2)
        attrs = match.group(3)
        # Parse attributes
        attrs_str = attrs.replace("'", '"').strip()
        return f'<input type="text" name="{field}" value="{{{{ old(\'{field}\', {value}) }}}}" {attrs_str}>'
    return re.sub(pattern, replacer, content, flags=re.DOTALL)

def convert_form_textarea(content):
    pattern = r'''\{!! Form::textarea\('([^']+)',\s*([^,]+),\s*\[(.*?)\]\);?\s*!!\}'''
    def replacer(match):
        field = match.group(1)
        value = match.group(2)
        attrs = match.group(3)
        attrs_str = attrs.replace("'", '"').strip()
        return f'<textarea name="{field}" {attrs_str}>{{{{ old(\'{field}\', {value}) }}}}</textarea>'
    return re.sub(pattern, replacer, content, flags=re.DOTALL)

def convert_form_select(content):
    # Pattern: Form::select('field', $options, value, [attrs])
    pattern = r'''\{!! Form::select\('([^']+)',\s*([^,]+),\s*([^,]+),\s*\[(.*?)\]\);?\s*!!\}'''
    def replacer(match):
        field = match.group(1)
        options = match.group(2)
        value = match.group(3)
        attrs = match.group(4)
        attrs_str = attrs.replace("'", '"').strip()
        return f'''<select name="{field}" {attrs_str}>
    @foreach({options} as $key => $val)
        <option value="{{{{ $key }}}}" {{{{ old('{field}', {value}) == $key ? 'selected' : '' }}}}>{{{{ $val }}}}</option>
    @endforeach
</select>'''
    return re.sub(pattern, replacer, content, flags=re.DOTALL)

def convert_form_checkbox(content):
    # Pattern: Form::checkbox('field', value, checked, [attrs])
    pattern = r'''\{!! Form::checkbox\('([^']+)',\s*([^,]+),\s*([^,]+),\s*\[(.*?)\]\);?\s*!!\}'''
    def replacer(match):
        field = match.group(1)
        val = match.group(2)  
        checked = match.group(3)
        attrs = match.group(4)
        attrs_str = attrs.replace("'", '"').strip()
        return f'<input type="checkbox" name="{field}" value="{val}" {{{{ old(\'{field}\', {checked}) ? \'checked\' : \'\' }}}} {attrs_str}>'
    return re.sub(pattern, replacer, content, flags=re.DOTALL)

def convert_form_file(content):
    # Pattern: Form::file('field', [attrs])
    pattern = r'''\{!! Form::file\('([^']+)',\s*\[(.*?)\]\);?\s*!!\}'''
    def replacer(match):
        field = match.group(1)
        attrs = match.group(2)
        attrs_str = attrs.replace("'", '"').strip()
        return f'<input type="file" name="{field}" {attrs_str}>'
    return re.sub(pattern, replacer, content, flags=re.DOTALL)

def convert_form_number(content):
    # Pattern: Form::number('field', value, [attrs])
    pattern = r'''\{!! Form::number\('([^']+)',\s*([^,]+),\s*\[(.*?)\]\);?\s*!!\}'''
    def replacer(match):
        field = match.group(1)
        value = match.group(2)
        attrs = match.group(3)
        attrs_str = attrs.replace("'", '"').strip()
        return f'<input type="number" name="{field}" value="{{{{ old(\'{field}\', {value}) }}}}" {attrs_str}>'
    return re.sub(pattern, replacer, content, flags=re.DOTALL)

def convert_form_labels(content):
    # Pattern: Form::label('field', 'Label Text')
    pattern = r'''\{!! Form::label\('([^']+)',\s*([^)]+)\s*\)\s*!!\}'''
    return re.sub(pattern, convert_form_label, content)

def convert_file(filepath):
    print(f"Converting {filepath}...")
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Apply conversions
    content = convert_form_labels(content)
    content = convert_form_text(content)
    content = convert_form_textarea(content)
    content = convert_form_select(content)
    content = convert_form_checkbox(content)
    content = convert_form_file(content)
    content = convert_form_number(content)
    
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)
    print(f"✓ Converted {filepath}")

if __name__ == "__main__":
    if len(sys.argv) > 1:
        convert_file(sys.argv[1])
    else:
        print("Usage: python convert_forms.py <file_path>")
