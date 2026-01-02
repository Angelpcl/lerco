import mysql.connector

# Define database configuration
db_config = {
    'user': 'root',
    'password': '',
    'host': 'localhost',
    'database': 'dev_originacion'
}



def generate_sql_inserts_auth_can(table_name, actions, description, group_id, sub_group, entity):
    sql_statements = []
    for action in actions:
        name = f"{entity}{action.capitalize()}"
        sql = f"""
        INSERT INTO `{table_name}` (`name`, `type`, `description`, `grupo_id`, `subgrupo`, `accion`)
        VALUES ('{name}', '2', '{description}', '{group_id}', '{sub_group}', '{action}');
        """
        sql_statements.append(sql.strip())
    return sql_statements


def write_sql_to_file(file_path, sql_statements):
    with open(file_path, 'a', encoding='utf-8') as file:
        for sql in sql_statements:
            file.write(sql + '\n')
    #print(f"SQL statements written to {file_path}")

def execute_sql_inserts(db_config, sql_statements):
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()
        for sql in sql_statements:
            cursor.execute(sql)
        conn.commit()
    except mysql.connector.Error as err:
        print(f"Error: {err}")
        conn.rollback()
    finally:
        cursor.close()
        conn.close()

if __name__ == "__main__":
    names_group = [
        ('pais','Paises'),
        ('promocionessuc','Promociones'),
        ('zonaroja','Zonas Rojas'),
        #('horario','Horario'),
        #('planeacion','Planeacion'),
        #('ciclo','Ciclo escolar'),
        ]
    
    table_name = 'auth_item'
    actions = ['view', 'update', 'create', 'delete']
    #description = 'Entidad'
    group_id = 15
    
    
    file_path = 'sql/auth_inserts.sql'
    
    with open(file_path, 'w', encoding='utf-8') as file:
        file.write("-- SQL Insert Statements\n")
    
    for name in names_group:
        sub_group = name[0]
        entity = name[0]
        description = name[1]
        sql_inserts = generate_sql_inserts_auth_can(table_name, actions, description, group_id, sub_group, entity)
        #execute_sql_inserts(db_config, sql_inserts)
        write_sql_to_file(file_path, sql_inserts)
        print(f"SQL inserts written to {file_path} for {name}.")
    
    
