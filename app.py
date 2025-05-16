from flask import Flask, render_template, jsonify, request
import pymysql as mysql
from gtts import gTTS
import os
from datetime import datetime

app = Flask(__name__)

db = mysql.connect(
    host='localhost',
    user='root',
    password='tu_clave',
    database='biblioteca'
)

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/validar_usuario/<documento>')
def validar_usuario(documento):
    cursor = db.cursor(dictionary=True)
    cursor.execute('SELECT * FROM usuarios WHERE documento = %s', (documento,))
    user = cursor.fetchone()
    return jsonify({'valido': user is not None})

@app.route('/oferta')
def oferta():
    cursor = db.cursor(dictionary=True)
    cursor.execute('SELECT * FROM cursos')
    cursos = cursor.fetchall()
    return jsonify(cursos)

@app.route('/matricular', methods=['POST'])
def matricular():
    data = request.json
    doc = data['documento']
    cursos = data['cursos']
    cursor = db.cursor()
    cursor.execute('SELECT id FROM usuarios WHERE documento = %s', (doc,))
    user_id = cursor.fetchone()[0]
    for cid in cursos:
        cursor.execute('INSERT INTO matriculas (id_usuario, id_curso) VALUES (%s, %s)', (user_id, cid))
    db.commit()

    cursor.execute('SELECT nombre FROM cursos WHERE id IN (%s)' % ','.join(['%s']*len(cursos)), cursos)
    nombres = [row[0] for row in cursor.fetchall()]
    resumen = f"Te has matriculado en los cursos: {', '.join(nombres)}."

    tts = gTTS(resumen, lang='es')
    fname = f"static/audio/matricula_{datetime.now().timestamp()}.mp3"
    tts.save(fname)

    return jsonify({'mensaje': resumen, 'audio': '/' + fname})

if __name__ == '__main__':
    app.run(debug=True)
