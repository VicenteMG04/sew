import xml.etree.ElementTree as ET

# Comienza el archivo HTML con la cabecera y el estilo de línea
def prologoHTML(outFile):
    outFile.write('<!DOCTYPE html>\n')
    outFile.write('<html lang="es">\n')
    outFile.write('<head>\n')
    outFile.write('  <meta charset="UTF-8">\n')
    outFile.write('  <meta name="author" content="Vicente Megido García (UO294013)" />\n')
    outFile.write('  <meta name="description" content="Menú con información relativa al circuito de Le Mans para la web MotoGP - Desktop, totalmente extraída del fichero circuitoEsquema.xml" />\n')
    outFile.write('  <meta name="keywords" content="MotoGP, motos, carreras, Francia, circuito, Le Mans, motociclismo" />\n')
    outFile.write('  <meta name="viewport" content="width=device-width, initial-scale=1.0">\n')
    outFile.write('  <title>MotoGp - Circuito de Le Mans</title>\n')
    outFile.write('  <link rel="stylesheet" type="text/css" href="estilo/estilo.css">\n')
    outFile.write('  <link rel="stylesheet" type="text/css" href="estilo/layout.css">\n')
    outFile.write('  <script src="js/menu.js"></script>\n')
    outFile.write('</head>\n')
    outFile.write('<body>\n')
    outFile.write('  <header>\n')
    outFile.write('    <h1><a href="index.html" title="Página principal">MotoGP Desktop</a></h1>\n')
    outFile.write('    <nav hidden>\n')
    outFile.write('      <a href="index.html" title="Página de inicio">Inicio</a>\n')
    outFile.write('      <a href="piloto.html" title="Información del piloto Fermín Aldeguer">Piloto</a>\n')
    outFile.write('      <a href="circuito.html" title="Información del circuito de Le Mans">Circuito</a>\n')
    outFile.write('      <a href="meteorologia.html" title="Información meteorológica">Meteorología</a>\n')
    outFile.write('      <a href="clasificaciones.php" title="Página de clasificaciones">Clasificaciones</a>\n')
    outFile.write('      <a href="juegos.html" title="Página de juegos">Juegos</a>\n')
    outFile.write('      <a href="ayuda.html" title="Página de ayuda">Ayuda</a>\n')
    outFile.write('    </nav>\n')
    outFile.write('  </header>\n')
    outFile.write('  <p>Estás en: <a href="index.html" title="Página de inicio">Inicio</a> >> <strong>Circuito</strong></p>\n')
    outFile.write('  <main>\n')

# Cierra el documento HTML
def epilogoHTML(outFile):
    outFile.write('  </main>\n')
    outFile.write('  <footer>\n')
    outFile.write('    <p>© MotoGP - Desktop | Software y Estándares para la Web (SEW), Curso 2025-2026 | Vicente Megido García (UO294013) - Todos los derechos reservados</p>\n')
    outFile.write('  </footer>\n')
    outFile.write('</body>\n')
    outFile.write('</html>\n')

# Escribe en el HTML la información del circuito
def escribeHTML(outFile, root, ns):
    nombre = root.get("nombre")
    pais = root.get("pais")
    localidad = root.get("localidad_proxima")
    vueltas = root.get("vueltas")

    longitud = root.find("ns:longitud_circuito", namespaces=ns)
    anchura = root.find("ns:anchura_media", namespaces=ns)
    fecha = root.find("ns:fecha_2025", namespaces=ns)
    hora = root.find("ns:hora_carrera_españa", namespaces=ns)
    patrocinador = root.find("ns:patrocinador", namespaces=ns)
    ganador = root.find("ns:vencedor/ns:piloto", namespaces=ns)
    tiempo = root.find("ns:vencedor/ns:tiempo", namespaces=ns)

    referencias = [r.text for r in root.findall("ns:referencias/ns:referencia", namespaces=ns)]
    fotos = [f.text for f in root.findall("ns:fotos/ns:foto", namespaces=ns)]
    videos = [v.text for v in root.findall("ns:videos/ns:video", namespaces=ns)]
    clasificacion = [p.text for p in root.findall("ns:clasificacion_mundial/ns:piloto", namespaces=ns)]

    outFile.write(f'    <h2>Circuito: {nombre}</h2>\n')
    outFile.write(f'    <p>Localidad próxima: {localidad} ({pais})</p>\n')
    outFile.write(f'    <p>Vueltas: {vueltas}</p>\n')
    outFile.write(f'    <p>Longitud del circuito: {longitud.text} {longitud.get("unidad")}</p>\n')
    outFile.write(f'    <p>Anchura media: {anchura.text} {anchura.get("unidad")}</p>\n')
    outFile.write(f'    <p>Fecha de la carrera: {fecha.text}</p>\n')
    outFile.write(f'    <p>Hora en España: {hora.text}</p>\n')
    outFile.write(f'    <p>Patrocinador: {patrocinador.text}</p>\n')
    outFile.write(f'    <p>Vencedor: {ganador.text} ({tiempo.text})</p>\n')

    outFile.write('    <h3>Clasificación Mundial</h3>\n')
    outFile.write('    <ol>\n')
    for piloto in clasificacion:
        outFile.write(f'      <li>{piloto}</li>\n')
    outFile.write('    </ol>\n')

    outFile.write('    <h3>Referencias</h3>\n')
    outFile.write('    <ul>\n')
    for ref in referencias:
        outFile.write(f'      <li><a href="{ref}">{ref}</a></li>\n')
    outFile.write('    </ul>\n')

    outFile.write('    <h3>Galería</h3>\n')
    for f in fotos:
        outFile.write(f'    <figure><img src="{f}" alt="Foto del circuito"></figure>\n')
    for v in videos:
        outFile.write(f'    <video controls><source src="{v}" type="video/mp4"></video>\n')

# Función principal que procesa el archivo XML y genera el archivo HTML
def main():
    try:
        tree = ET.parse("circuitoEsquema.xml")
    except Exception as e:
        print("No se puede abrir 'circuitoEsquema.xml':", e)
        return

    ns = {'ns': 'http://www.uniovi.es'}
    root = tree.getroot() # Raíz del XML, <circuito>

    html_filename = "../InfoCircuito.html"
    with open(html_filename, "w", encoding="utf-8") as outFile:
        prologoHTML(outFile)
        escribeHTML(outFile, root, ns)
        epilogoHTML(outFile)

    print(f"HTML generado correctamente: {html_filename}")

if __name__ == "__main__":
    main()
