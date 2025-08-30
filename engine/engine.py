import sys
import time
import pymysql

db_connection = pymysql.connect(
    host="185.213.26.79",
    user="clyde",
    password="PurpleHorse@01",
    database="mcg_test"
)

class GameEngine:
    def second_tick(self):
        if not hasattr(self, 'second_tick_counter'):
            self.second_tick_counter = 0
        self.second_tick_counter += 1
        print(f"second_tick {self.second_tick_counter}")
    def minute_tick(self):
        player_array = get_player_array()
        print(player_array)
        for player in player_array:
            player_id = player[0]
            self.update_resources(player_id)
    def update_resources(player_id):
            print('in update resources')
            try:
                cursor = db_connection.cursor()
                cursor.execute("UPDATE player_resources SET quantity = quantity + 10 WHERE player_id = %s", (player_id,))
                db_connection.commit()
                print("Database updated successfully for player_id:", player_id)
            except Exception as e:
                print(f"Database update failed for player_id {player_id}: {e}")
            finally:
                if 'cursor' in locals():
                    cursor.close()
    def __init__(self):
        self.running = True
        
        
    def process_events(self):
        # Placeholder for event processing
        pass

    def update(self):
        # Placeholder for game logic update
        #self.update_resources()
        pass

    def render(self):
        # Placeholder for rendering
        #print("Rendering frame...")
        pass
    def run(self):
        print("Starting game engine...")
        last_tick = time.time()
        last_minute_tick = time.time()
        while self.running:
            self.process_events()
            self.update()
            self.render()
            current_time = time.time()
            #one second timer
            if current_time - last_tick >= 1.0:
                self.second_tick()
                self.minute_tick()
                #print("One second has passed!")
                last_tick = current_time
            # One minute timer
            if current_time - last_minute_tick >= 60.0:
                self.minute_tick()
                print("One minute has passed!")
                last_minute_tick = current_time
            time.sleep(1/60)  # Run at ~60 FPS

    def stop(self):
        self.running = False

def get_player_array():
        try:
            cursor = db_connection.cursor()
            cursor.execute("SELECT player_id FROM players;")
            players = cursor.fetchall()
            cursor.close()
            return players
        except Exception as e:
            print(f"Failed to retrieve players: {e}")
            return []

if __name__ == "__main__":
    engine = GameEngine()
    try:
        engine.run()
    except KeyboardInterrupt:
        engine.stop()
        print("Game engine stopped.")
