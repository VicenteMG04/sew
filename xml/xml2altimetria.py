import xml.etree.ElementTree as ET

# Importamos la clase Svg (ejemplos de teoría, Tema 6: 02030-SVG.py)
# Clase modificada para evitar errores en el validador de HTML
class Svg(object):
    """Genera archivos SVG con rectángulos, círculos, líneas, polilíneas y texto"""
    def __init__(self):
        self.raiz = ET.Element('svg', xmlns="http://www.w3.org/2000/svg")

    def addRect(self, x, y, width, height, fill, stroke):
        ET.SubElement(self.raiz, 'rect', x=x, y=y, width=width, height=height, fill=fill, stroke=stroke)

    def addCircle(self, cx, cy, r, fill):
        ET.SubElement(self.raiz, 'circle', cx=cx, cy=cy, r=r, fill=fill)

    def addLine(self, x1, y1, x2, y2, stroke):
        ET.SubElement(self.raiz, 'line', x1=x1, y1=y1, x2=x2, y2=y2, stroke=stroke)

    def addPolyline(self, points, stroke, fill):
        ET.SubElement(self.raiz, 'polyline', points=points, stroke=stroke, fill=fill)

    def addText(self, texto, x, y, style):
        ET.SubElement(self.raiz, 'text', x=x, y=y, style=style).text = texto

    def escribir(self, nombreArchivoSVG):
        arbol = ET.ElementTree(self.raiz)
        ET.indent(arbol)
        arbol.write(nombreArchivoSVG, encoding='utf-8', xml_declaration=True)


# Función para leer las coordenadas y altitudes del XML
def parse_xml(xml_file):
    try:
        tree = ET.parse(xml_file)
    except Exception as e:
        print(f"Error al leer '{xml_file}': {e}")
        return []

    root = tree.getroot()
    ns = {'ns': 'http://www.uniovi.es'}
    coords_circuito = []

    # Coordenadas de inicio
    coord_inic = root.find(".//ns:coordenadas_inicio/ns:coordenada", namespaces=ns)
    if coord_inic is not None:
        coords_circuito.append({
            "lat": float(coord_inic.find(".//ns:latitud", namespaces=ns).text),
            "lon": float(coord_inic.find(".//ns:longitud", namespaces=ns).text),
            "alt": float(coord_inic.find(".//ns:altitud", namespaces=ns).text)
        })

    # Coordenadas de cada tramo
    for tramo in root.findall(".//ns:tramos/ns:tramo", namespaces=ns):
        coord_tramo = tramo.find(".//ns:coordenada", namespaces=ns)
        if coord_tramo is not None:
            coords_circuito.append({
                "lat": float(coord_tramo.find(".//ns:latitud", namespaces=ns).text),
                "lon": float(coord_tramo.find(".//ns:longitud", namespaces=ns).text),
                "alt": float(coord_tramo.find(".//ns:altitud", namespaces=ns).text)
            })

    return coords_circuito


# Función para crear el SVG usando la clase Svg
def crear_svg(coords_circuito, width=800, height=400, margin=50, ejeColor="black"):
    altitudes = [p['alt'] for p in coords_circuito]
    min_alt, max_alt = min(altitudes), max(altitudes)
    rango_alt = max_alt - min_alt
    step_x = (width - 2*margin) / (len(coords_circuito)-1)

    # Utilizamos el objeto SVG del ejemplo de teoría
    svg = Svg()
    # Fondo blanco
    svg.addRect('0', '0', str(width), str(height), 'white', 'white')

    # Ejes X e Y
    eje_x_y = height - margin
    svg.addLine(str(margin), str(eje_x_y), str(width-margin), str(eje_x_y), stroke=ejeColor) # Eje X
    svg.addLine(str(margin), str(margin/2), str(margin), str(eje_x_y), stroke=ejeColor) # Eje Y

    # Marcas y etiquetas en el eje Y (altitud)
    num_marcas_y = 5
    for i in range(num_marcas_y + 1):
        y_val = min_alt + i * (rango_alt / num_marcas_y)
        y_pos = eje_x_y - (i * (height - margin*1.5)/num_marcas_y)
        svg.addLine(str(margin-5), str(y_pos), str(margin+5), str(y_pos), stroke=ejeColor)
        svg.addText(f"{y_val:.0f} m", str(margin-50), str(y_pos+5), style="fill:black;")

    # Generar polilínea del perfil
    points = []
    for i, p in enumerate(coords_circuito):
        x = margin + i * step_x
        escala = (p['alt'] - min_alt) / rango_alt if rango_alt != 0 else 0
        y = (height - margin) - escala * (height - 2*margin)
        points.append(f"{x},{y}")

    # Cerrar polilínea ("suelo" del gráfico)
    base_y = height - 10
    points_str = f"{margin},{base_y} " + " ".join(points) + f" {width-margin},{base_y} {margin},{base_y}"
    svg.addPolyline(points_str, stroke='red', fill='pink')

    # Dibujar círculos en los puntos
    for i, p in enumerate(coords_circuito):
        x = margin + i * step_x
        escala = (p['alt'] - min_alt) / rango_alt if rango_alt != 0 else 0
        y = (height - margin) - escala * (height - 2*margin)
        svg.addCircle(str(x), str(y), '2', 'red')

    return svg


# Función principal
def main():
    xml_file = "circuitoEsquema.xml"
    coords_circuito = parse_xml(xml_file)
    if not coords_circuito:
        print("No se pudo extraer datos del XML.")
        return

    svg = crear_svg(coords_circuito)
    svg.escribir("altimetria.svg")
    print("SVG generado correctamente: altimetria.svg")


if __name__ == "__main__":
    main()
