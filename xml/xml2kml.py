import xml.etree.ElementTree as ET

# Comienza el archivo KML con la cabecera y el estilo de línea
def prologoKML(outFile):
    outFile.write('<?xml version="1.0" encoding="UTF-8"?>\n')
    outFile.write('<kml xmlns="http://www.opengis.net/kml/2.2">\n')
    outFile.write("  <Document>\n")
    # Estilo para la línea roja
    outFile.write('    <Style id="lineaRoja">\n')
    outFile.write("      <LineStyle><color>#ff0000ff</color><width>3</width></LineStyle>\n")
    outFile.write("    </Style>\n\n")

# Cierra el documento KML
def epilogoKML(outFile):
    outFile.write("  </Document>\n")
    outFile.write("</kml>\n")

# Escribe la línea en el KML con la lista de coordenadas del circuito
def escribeLinea(outFile, nombre, coords):
    outFile.write("    <Placemark>\n")
    outFile.write(f"      <name>{nombre}</name>\n")
    outFile.write("      <styleUrl>#lineaRoja</styleUrl>\n")
    outFile.write("      <LineString>\n")
    outFile.write("        <extrude>1</extrude>\n")
    outFile.write("        <tessellate>1</tessellate>\n")
    outFile.write("        <coordinates>\n")
    for lon, lat in coords:
        outFile.write(f"          {lon},{lat}\n")
    outFile.write("        </coordinates>\n")
    outFile.write("      </LineString>\n")
    outFile.write("    </Placemark>\n\n")

# Escribe un punto en el KML con sus coordenadas
def escribePunto(outFile, nombre, lon, lat):
    outFile.write("    <Placemark>\n")
    outFile.write(f"      <name>{nombre}</name>\n")
    outFile.write("      <Point>\n")
    outFile.write(f"        <coordinates>{lon},{lat},0</coordinates>\n")
    outFile.write("      </Point>\n")
    outFile.write("    </Placemark>\n\n")

# Función principal que procesa el archivo XML y genera el archivo KML
def main():
    try:
        tree = ET.parse("circuitoEsquema.xml")
    except Exception as e:
        print("No se puede abrir 'circuitoEsquema.xml':", e)
        return

    ns = {'ns': 'http://www.uniovi.es'}
    root = tree.getroot() # Raíz del XML, <circuito>

    nombre = root.get("nombre") # IMPORTANTE: usar .get() para atributos y .find() para elementos
    kml_filename = "circuito.kml"

    with open(kml_filename, "w", encoding="utf-8") as outFile:
        prologoKML(outFile)

        coord_inic = root.find(".//ns:coordenadas_inicio/ns:coordenada", namespaces=ns)
        coords_line = []
        if coord_inic is not None:
            lon_inic = coord_inic.find(".//ns:longitud", namespaces=ns).text
            lat_inic = coord_inic.find(".//ns:latitud", namespaces=ns).text
            coords_line.append((lon_inic, lat_inic))
            escribePunto(outFile, "Línea de salida", lon_inic, lat_inic)

        for tramo in root.findall(".//ns:tramos/ns:tramo", namespaces=ns):
            coord_tramo = tramo.find(".//ns:coordenada", namespaces=ns)
            if coord_tramo is not None:
                lon_tramo = coord_tramo.find(".//ns:longitud", namespaces=ns).text
                lat_tramo = coord_tramo.find(".//ns:latitud", namespaces=ns).text
                coords_line.append((lon_tramo, lat_tramo))
                # escribePunto(outFile, lon_tramo, lat_tramo)

        escribeLinea(outFile, nombre, coords_line)

        epilogoKML(outFile)

    print(f"KML generado: {kml_filename}")

if __name__ == "__main__":
    main()
